<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\SalonCategory;
use App\Models\SalonImage;
use App\Models\SalonProfile;
use App\Models\SalonService;
use App\Models\User;
use App\Services\MediaService;
use App\Services\SalonContent;
use App\Services\SiteSettingsService;
use Database\Seeders\SavyNailsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SavyCleanup extends Command
{
    protected $signature = 'savy:cleanup {--apply : Back up and apply the Savy brand conversion} {--replace-legacy-menu : Replace a detected legacy menu with the supplied Savy menu}';

    protected $description = 'Convert retained salon content to Savy without resetting an existing Savy menu or admin credentials';

    // Migration recognition only; this brand is never a rendering fallback.
    private const LEGACY = '~\bthe[\s_-]*k\b|thek|fayetteville|910[\s().+-]*491[\s().-]*0358|2804 Freedom|61594988307910|rZcnSx2muwHrotw88~i';

    public function handle(): int
    {
        $this->line('Database: '.DB::connection()->getDatabaseName());
        if (! str_contains(strtolower(DB::connection()->getDatabaseName()), 'savy')) {
            $this->error('Select a separate Savy database before running this command.');

            return self::FAILURE;
        }
        $legacyMenu = ! SalonCategory::whereIn('slug', ['manicure-nail-enhancements', 'pedicure-services'])->exists()
            && SalonCategory::whereIn('slug', ['nails', 'pedicures', 'brows-lashes', 'kids'])->exists()
            && SalonService::whereIn('name', ['Golden 99K Fairy Queen', 'Fresh Orange Pedicure'])->exists();
        $wasSavyProfile = ! empty(SalonProfile::find(1)?->data['savy_revision']);
        $beforeSettings = app(SiteSettingsService::class)->all();
        $legacyBrand = $legacyMenu || $this->containsLegacy([$beforeSettings['site_name'], $beforeSettings['company_name'], $beforeSettings['address']]);
        $this->line('Services retained: '.SalonService::count());
        if (! $this->option('apply')) {
            $this->info('Dry run. Use --apply to create a private backup and convert content.');

            return self::SUCCESS;
        }
        if ($legacyMenu && ! $this->option('replace-legacy-menu')) {
            $this->error('Legacy menu detected. Review it first; use --replace-legacy-menu only to replace it with the approved Savy menu.');

            return self::FAILURE;
        }
        if (! is_file(public_path('assets/optimized/report.json')) || ! is_file(public_path('build/manifest.json'))) {
            $this->error('Upload the new Savy build and optimized assets before cleanup.');

            return self::FAILURE;
        }
        $tables = ['pages', 'page_revisions', 'site_settings', 'salon_profiles', 'salon_images', 'salon_categories', 'salon_services', 'navigation_items', 'media', 'redirects'];
        $snapshot = ['database' => DB::connection()->getDatabaseName(), 'created_at' => now()->toIso8601String(), 'tables' => []];
        foreach ($tables as $table) {
            $snapshot['tables'][$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
        }
        $backup = 'backups/savy-before-cleanup-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(3)).'.json';
        if (! Storage::disk('local')->put($backup, json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT))) {
            $this->error('Cannot create the private content backup. No content was changed.');

            return self::FAILURE;
        }
        $this->line('Private content backup: '.$backup);

        DB::transaction(function () use ($legacyMenu, $legacyBrand, $wasSavyProfile, $backup): void {
            if ($legacyMenu) {
                SalonService::query()->delete();
                SalonCategory::query()->delete();
            }
            $seed = new SavyNailsSeeder;
            $settings = app(SiteSettingsService::class);
            $current = $settings->all();
            $ogMedia = Media::find($current['og_media_id']);
            if ($ogMedia && $this->isRetiredMedia($ogMedia)) {
                $settings->update(array_replace($current, ['og_media_id' => null]));
            }
            foreach (Page::with('ogMedia')->get() as $page) {
                if ($page->ogMedia && $this->isRetiredMedia($page->ogMedia)) {
                    $page->update(['og_media_id' => null]);
                }
            }
            $seed->run();
            $current = $settings->all();
            $replaceEmbed = $this->containsLegacy($current['maps_url'] ?? null);
            foreach ($seed->brandSettings() as $key => $value) {
                if ($this->containsLegacy($current[$key] ?? null)) {
                    $current[$key] = $value;
                }
            }
            if ($replaceEmbed) {
                $current['maps_embed_url'] = $seed->brandSettings()['maps_embed_url'];
            }
            // Logo artwork comes from the Savy wordmark, including admin/favicon fallbacks.
            foreach (['logo_id', 'mobile_logo_id', 'favicon_id'] as $key) {
                if ($id = $current[$key] ?? null) {
                    $media = DB::table('media')->find($id);
                    if ($media && (preg_match(self::LEGACY, json_encode($media)) || preg_match('~brand-logo|site-logo-(?:v1|pearl|client)~i', $media->name))) {
                        $current[$key] = null;
                    }
                }
            }
            $settings->update($this->cleanStrings($current));

            $profile = SalonProfile::findOrFail(1);
            $defaults = $seed->profileDefaults();
            $data = $this->mergeBrandValues($profile->data, $defaults);
            if ($legacyBrand && ! $wasSavyProfile) {
                foreach (['city', 'short_city', 'timezone', 'postal_address', 'hours', 'hours_note'] as $key) {
                    $data[$key] = $defaults[$key];
                }
            }
            $data['booking_url'] = null;
            $data['copy']['global']['booking_url'] = '/contact-us';
            $data['copy']['global']['booking_label'] = 'Contact to Book';
            foreach (['hero_image', 'hero_mobile_image', 'hero_slides', 'story_image', 'about_image', 'about_brand_image', 'contact_image', 'home_lookbook'] as $key) {
                $value = $data[$key] ?? null;
                if ($value && collect(is_array($value) ? $value : [$value])->contains(function ($key): bool {
                    $image = SalonImage::where('key', $key)->first();

                    return $image && str_contains(json_encode($image->variants, JSON_UNESCAPED_SLASHES), 'the-k/');
                })) {
                    $data[$key] = $defaults[$key];
                }
            }
            foreach (['menu_brow_images', 'ritual_image', 'booking_image', 'client_design_revision', 'client_menu_revision', 'menu_revision', 'brand_revision', 'design_revision', 'luxury_revision', 'ritual_revision', 'client_media_revision', 'hero_slideshow_revision', 'about_brand_image_revision', 'original_logo_restore_revision'] as $key) {
                unset($data[$key]);
            }
            $profile->update(['data' => $this->cleanStrings($data)]);

            foreach (['home', 'about-us', 'services', 'gallery', 'contact-us'] as $slug) {
                $page = Page::where('slug', $slug)->firstOrFail();
                $defaults = $seed->pageContent($slug);
                $changes = ['template' => $defaults['template']];
                if ($page->heroMedia && $this->isRetiredMedia($page->heroMedia)) {
                    $changes['hero_media_id'] = null;
                }
                foreach (['title', 'seo_title', 'seo_description', 'sections'] as $key) {
                    if ($this->containsLegacy($page->{$key}) || ! $page->{$key}) {
                        $changes[$key] = $defaults[$key];
                    }
                }
                $page->update($changes);
            }
            // Keep retired IDs if referenced by leads; strip obsolete branding from their content.
            foreach (Page::whereNotIn('slug', ['home', 'about-us', 'services', 'gallery', 'contact-us'])->get() as $page) {
                if ($this->containsLegacy($page->attributesToArray())) {
                    $page->update(['title' => 'Retired page', 'seo_title' => null, 'seo_description' => null, 'hero_media_id' => null, 'og_media_id' => null, 'sections' => [], 'status' => 'draft', 'exportable' => false]);
                }
            }
            foreach (PageRevision::cursor() as $revision) {
                if ($this->containsLegacy($revision->snapshot) || str_contains(json_encode($revision->snapshot), 'the-k/')) {
                    $revision->delete();
                }
            }

            $oldImages = SalonImage::where('key', 'not like', 'savy-%')->get()->filter(fn ($image) => str_contains(json_encode($image->variants, JSON_UNESCAPED_SLASHES), 'the-k/'));
            foreach ($oldImages as $image) {
                foreach (SalonCategory::where('image_id', $image->id)->get() as $category) {
                    $key = match ($category->slug) {
                        'pedicure-services' => 'savy-pedicure', 'additions' => 'savy-additions', 'waxing' => 'savy-waxing', default => 'savy-hero',
                    };
                    $category->update(['image_id' => SalonImage::where('key', $key)->value('id')]);
                }
                SalonService::where('image_id', $image->id)->update(['image_id' => null]);
                // Public references were remapped; old revision snapshots are backed up above.
                if (! $image->inUse()) {
                    $image->delete();
                }
            }
            foreach (User::where('is_admin', true)->get() as $admin) {
                if (preg_match(self::LEGACY, $admin->name)) {
                    $admin->name = 'Savy Administrator';
                    $admin->save();
                }
            }
            foreach (DB::table('redirects')->get() as $redirect) {
                if ($this->containsLegacy([$redirect->source, $redirect->target])) {
                    DB::table('redirects')->where('id', $redirect->id)->delete();
                }
            }
            foreach (Media::cursor() as $media) {
                if (! $this->isRetiredMedia($media) || app(MediaService::class)->inUse($media)) {
                    continue;
                }
                // Archive the bytes privately before the existing media service removes them.
                $files = [['local', $media->original_path]];
                foreach ($media->variants ?? [] as $variant) {
                    $files[] = [$media->disk, $variant['path']];
                }
                foreach ($files as [$disk, $path]) {
                    if (Storage::disk($disk)->exists($path)) {
                        if (! Storage::disk('local')->put($backup.'.media/'.$media->id.'/'.$disk.'/'.basename($path), Storage::disk($disk)->get($path))) {
                            throw new \RuntimeException('Retired media backup failed.');
                        }
                    }
                }
                app(MediaService::class)->delete($media);
            }
        });
        app(SalonContent::class)->forget();
        Cache::flush();
        $remaining = [];
        foreach ($tables as $table) {
            foreach (DB::table($table)->cursor() as $row) {
                if ($this->containsLegacy((array) $row)) {
                    $remaining[] = $table.'#'.$row->id;
                }
            }
        }
        if ($remaining) {
            $this->error('Remaining legacy content needs review: '.implode(', ', $remaining));

            return self::FAILURE;
        }
        $this->archiveRetiredAssets($backup);
        $this->info('Savy cleanup complete. Admin email/password and existing Savy service IDs/prices were preserved.');

        return self::SUCCESS;
    }

    private function containsLegacy(mixed $value): bool
    {
        return (bool) preg_match(self::LEGACY, json_encode($value, JSON_UNESCAPED_SLASHES));
    }

    private function archiveRetiredAssets(string $backup): void
    {
        $publicRoot = realpath(public_path());
        foreach (['assets/optimized/the-k', 'assets/the-k'] as $relative) {
            $path = public_path($relative);
            if (! is_dir($path)) {
                continue;
            }
            $resolved = realpath($path);
            if (is_link($path) || ! $resolved || ! str_starts_with($resolved, $publicRoot.DIRECTORY_SEPARATOR)) {
                throw new \RuntimeException('Unsafe retired asset directory.');
            }
            if (! File::copyDirectory($resolved, Storage::disk('local')->path($backup.'.assets/'.$relative))) {
                throw new \RuntimeException('Retired asset backup failed.');
            }
            if (! File::deleteDirectory($resolved)) {
                throw new \RuntimeException('Cannot remove an archived retired asset directory.');
            }
        }
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true, flags: JSON_THROW_ON_ERROR);
        $allowed = [];
        foreach ($manifest as $entry) {
            foreach ([$entry['file'] ?? '', ...($entry['css'] ?? []), ...($entry['assets'] ?? [])] as $file) {
                $allowed[$file] = true;
            }
        }
        $buildRoot = realpath(public_path('build'));
        foreach (File::allFiles($buildRoot) as $file) {
            if ($file->isLink()) {
                continue;
            }
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            if ($relative === 'manifest.json' || isset($allowed[$relative])) {
                continue;
            }
            $resolved = $file->getRealPath();
            if (! str_starts_with($resolved, $buildRoot.DIRECTORY_SEPARATOR)) {
                throw new \RuntimeException('Unsafe compiled asset path.');
            }
            if (! Storage::disk('local')->put($backup.'.assets/build/'.$relative, file_get_contents($resolved))) {
                throw new \RuntimeException('Obsolete build asset backup failed.');
            }
            if (! File::delete($resolved)) {
                throw new \RuntimeException('Cannot remove an archived build asset.');
            }
        }
    }

    private function isRetiredMedia(Media $media): bool
    {
        return $this->containsLegacy([$media->name, $media->alt])
            || (bool) preg_match('~^(?:brand-logo|site-logo-(?:v1|pearl|client)|hero-manicure)~i', $media->name);
    }

    private function mergeBrandValues(array $existing, array $defaults): array
    {
        foreach ($defaults as $key => $value) {
            if (! array_key_exists($key, $existing) || $existing[$key] === null || $existing[$key] === '') {
                $existing[$key] = $value;
            } elseif (is_array($value) && is_array($existing[$key])) {
                $existing[$key] = $this->mergeBrandValues($existing[$key], $value);
            } elseif ($this->containsLegacy($existing[$key])) {
                $existing[$key] = $value;
            }
        }

        return $existing;
    }

    private function cleanStrings(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map($this->cleanStrings(...), $value);
        }
        if (! is_string($value)) {
            return $value;
        }

        return preg_replace(['~THE[\s_-]*K(?:\s+LUXURY\s+NAIL\s+BAR)?~i', '~thek~i', '~Fayetteville(?:,?\s*(?:North Carolina|NC))?~i'], ['Savy Nails & Spa', 'savy', 'Walla Walla, WA'], $value);
    }
}

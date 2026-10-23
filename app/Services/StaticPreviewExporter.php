<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Page;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

class StaticPreviewExporter
{
    public function export(string $name = 'preview'): array
    {
        if (is_file(public_path('hot'))) {
            throw new \RuntimeException('Stop Vite dev server and remove public/hot before exporting.');
        }
        if (! is_file(public_path('build/manifest.json'))) {
            throw new \RuntimeException('Run npm run build first.');
        }
        $pages = Page::publiclyAccessible()->where('exportable', true)->get();
        if ($pages->isEmpty()) {
            throw new \RuntimeException('No published page explicitly marked exportable.');
        }
        if (! preg_match('/^[a-z][a-z0-9-]{0,60}$/', $name)) {
            throw new \RuntimeException('Unsafe export directory name.');
        }
        $destination = base_path('dist/'.$name);
        // A fixed destination and ownership marker prevent deleting arbitrary directories.
        if (is_link(base_path('dist')) || is_link($destination)) {
            throw new \RuntimeException('Export destination must not be a symlink.');
        }
        if (File::exists($destination)) {
            if (! File::exists($destination.'/.starter-preview')) {
                throw new \RuntimeException('Existing export directory has no ownership marker. Move it before exporting.');
            }
            foreach (File::allFiles($destination) as $file) {
                if ($file->isLink()) {
                    throw new \RuntimeException('Symlinks in export destination are not allowed.');
                }
            }
            File::deleteDirectory($destination);
        }
        File::ensureDirectoryExists($destination);
        File::put($destination.'/.starter-preview', 'Generated public preview; safe to regenerate.');
        $old = config('starter.exporting');
        $assetUrl = config('app.asset_url');
        config(['starter.exporting' => true, 'app.asset_url' => null]);
        URL::forceRootUrl('http://localhost');
        View::share('errors', new ViewErrorBag);
        $exported = [];
        try {
            foreach ($pages as $page) {
                $path = $page->path();
                if (! preg_match('~^/(?:[a-z0-9]+(?:[/-][a-z0-9]+)*)?$~', $path)) {
                    throw new \RuntimeException('Unsafe page path');
                }
                $html = app(PageRenderer::class)->render($page, true)->render();
                // Asset URLs become root-relative; canonical and schema retain production origin.
                $html = preg_replace_callback('~\b(?:src|srcset|href)=["\']([^"\']*)["\']~', fn ($match) => preg_replace('~https?://[^/\s"\'<>]+/(build|storage|assets)/~', '/$1/', $match[0]), $html);
                $output = $destination.($path === '/' ? '' : $path).'/index.html';
                File::ensureDirectoryExists(dirname($output));
                File::put($output, $html);
                $exported[] = $path;
            }
            $this->copyAssets(public_path('build'), $destination.'/build', ['js', 'css', 'woff', 'woff2', 'ttf', 'png', 'jpg', 'jpeg', 'webp', 'avif', 'svg', 'json']);
            $this->copyAssets(public_path('assets'), $destination.'/assets', ['svg', 'png', 'jpg', 'jpeg', 'webp', 'avif', 'gif', 'woff2']);
            // Only media referenced by exported pages or shared public branding is copied.
            $ids = collect(app(SiteSettingsService::class)->all())->only(['logo_id', 'mobile_logo_id', 'favicon_id', 'og_media_id'])->values();
            foreach ($pages as $page) {
                $ids = $ids->merge([$page->hero_media_id, $page->og_media_id]);
                foreach ($page->sections ?? [] as $section) {
                    $ids->push($section['media_id'] ?? null);
                    foreach ($section['items'] ?? [] as $item) {
                        $ids->push($item['media_id'] ?? null);
                    }
                }
            }
            foreach (Media::whereIn('id', $ids->filter()->unique())->get() as $media) {
                if ($media->disk !== 'public') {
                    throw new \RuntimeException('Static export currently requires public local media. Configure an explicit CDN adapter for other disks.');
                }
                foreach ($media->variants ?? [] as $variant) {
                    $path = $variant['path'];
                    if (! preg_match('~^media/[0-9]+/[0-9]+\.(webp|png)$~', $path)) {
                        throw new \RuntimeException('Unexpected media variant path.');
                    }
                    File::ensureDirectoryExists(dirname($destination.'/storage/'.$path));
                    File::copy(Storage::disk('public')->path($path), $destination.'/storage/'.$path);
                }
            }
            File::put($destination.'/robots.txt', "User-agent: *\nDisallow: /\n");
            $english = app()->getLocale() === 'en';
            File::put($destination.'/404.html', '<!doctype html><html lang="'.($english ? 'en' : 'vi').'"><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><title>'.($english ? 'Page not found' : 'Không tìm thấy trang').'</title><h1>'.($english ? 'This page is not in the preview.' : 'Trang không có trong bản demo.').'</h1><a href="/">'.($english ? 'Back to home' : 'Về trang chủ').'</a></html>');
        } finally {
            config(['starter.exporting' => $old, 'app.asset_url' => $assetUrl]);
            URL::forceRootUrl(null);
        }

        return $exported;
    }

    private function copyAssets(string $source, string $destination, array $extensions): void
    {
        if (! File::isDirectory($source)) {
            return;
        }
        foreach (File::allFiles($source) as $file) {
            if ($file->isLink() || ! in_array(strtolower($file->getExtension()), $extensions, true)) {
                continue;
            }
            $target = $destination.'/'.$file->getRelativePathname();
            File::ensureDirectoryExists(dirname($target));
            File::copy($file->getPathname(), $target);
        }
    }
}

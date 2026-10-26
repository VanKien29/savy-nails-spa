<?php

namespace App\Models;

use App\Services\SectionRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Page extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['sections' => 'array', 'alternates' => 'array', 'published_at' => 'datetime', 'is_home' => 'boolean', 'noindex' => 'boolean', 'exportable' => 'boolean'];
    }

    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'hero_media_id');
    }

    public function ogMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_media_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopePubliclyAccessible(Builder $query): Builder
    {
        $retired = array_map(fn ($path) => trim($path, '/'), config('starter.retired_paths', []));

        return $query->published()
            ->whereIn('template', array_keys(config('starter.templates')))
            ->whereNotIn('template', config('starter.retired_templates', []))
            ->where(fn ($q) => $q->where('is_home', true)->orWhereNotIn('slug', $retired))
            ->where(function ($q) {
                $q->where(fn ($home) => $home->where('is_home', true)->whereNotExists(fn ($redirect) => $redirect->selectRaw('1')->from('redirects')->where('source', '/')))
                    ->orWhere(fn ($inner) => $inner->where('is_home', false)->whereNotIn('slug', Redirect::selectRaw('SUBSTR(source, 2)')));
            });
    }

    public function path(): string
    {
        return $this->is_home ? '/' : '/'.$this->slug;
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            Validator::make($page->attributesToArray(), [
                'title' => ['required', 'string', 'max:160'],
                'slug' => ['required', 'max:180', 'regex:~^[a-z0-9]+(?:[/-][a-z0-9]+)*$~', Rule::unique('pages', 'slug')->ignore($page->id)],
                'template' => ['required', Rule::in(array_keys(config('starter.templates')))],
                'status' => ['required', Rule::in(['draft', 'published'])],
                'seo_title' => ['nullable', 'string', 'max:160'], 'seo_description' => ['nullable', 'string', 'max:320'],
                'alternates' => ['nullable', 'array'], 'alternates.*.locale' => ['required', 'regex:/^[a-z]{2}(?:-[A-Z]{2})?$/'],
                'alternates.*.url' => ['required', 'url:http,https'],
                'hero_media_id' => ['nullable', 'exists:media,id'], 'og_media_id' => ['nullable', 'exists:media,id'],
            ])->validate();
            if (preg_match('~^(admin|livewire|starter|build|assets|storage|sitemap\.xml|robots\.txt|contact|up)(/|$)~', $page->slug)) {
                throw ValidationException::withMessages(['slug' => 'Đường dẫn dành riêng cho hệ thống.']);
            }
            if ($page->is_home && static::where('is_home', true)->whereKeyNot($page->id ?? 0)->exists()) {
                throw ValidationException::withMessages(['is_home' => 'Chỉ được có một trang chủ.']);
            }
            $page->sections = app(SectionRegistry::class)->validate($page->sections ?? []);
            $ids = collect($page->sections)->pluck('id')->filter();
            if ($ids->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages(['sections' => 'Mã khối không được trùng trong cùng một trang.']);
            }
        });
        static::updating(function (Page $page) {
            if ($page->isDirty(['title', 'slug', 'template', 'status', 'published_at', 'sections', 'seo_title', 'seo_description', 'hero_media_id', 'og_media_id', 'noindex', 'alternates'])) {
                PageRevision::create(['page_id' => $page->id, 'user_id' => auth()->id(), 'snapshot' => collect($page->getOriginal())->except(['id', 'created_at', 'updated_at'])->all()]);
                $keep = PageRevision::where('page_id', $page->id)->latest('id')->limit(30)->pluck('id');
                PageRevision::where('page_id', $page->id)->whereNotIn('id', $keep)->delete();
            }
            if (($page->isDirty('slug') || $page->isDirty('is_home')) && $page->getOriginal('status') === 'published') {
                $old = $page->getOriginal('is_home') ? '/' : '/'.$page->getOriginal('slug');
                if ($old === $page->path()) {
                    return;
                }
                Redirect::where('source', $page->path())->delete();
                Redirect::where('target', $old)->update(['target' => $page->path()]);
                Redirect::updateOrCreate(['source' => $old], ['target' => $page->path()]);
            }
        });
        static::saved(function (Page $page) {
            if ($page->status === 'published' && (! $page->published_at || $page->published_at->isPast())) {
                Redirect::where('source', $page->path())->delete();
            }
        });
    }
}

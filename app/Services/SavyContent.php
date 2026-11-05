<?php

namespace App\Services;

use App\Models\Page;

class SavyContent
{
    public function area(Page $page): string
    {
        return $page->is_home ? 'home' : match ($page->slug) {
            'about-us' => 'about', 'contact-us' => 'contact', default => $page->slug,
        };
    }

    public function blocks(Page $page): array
    {
        $saved = collect($page->sections ?? [])->keyBy('id');

        return collect(config('savy.pages.'.$this->area($page), []))->mapWithKeys(function ($defaults, $id) use ($saved) {
            $key = 'savy-'.$id;

            // Replace entire lists so removing the last image/FAQ/card stays removed.
            return [$key => array_replace(['id' => $key, 'type' => 'about', 'variant' => 'default', 'enabled' => true], $defaults, $saved->get($key, []))];
        })->all();
    }

    public function profile(array $profile): array
    {
        $saved = $profile['savy'] ?? [];
        $defaults = config('savy.profile');

        return array_replace($defaults, $saved, [
            'images' => array_replace($defaults['images'], $saved['images'] ?? []),
            'footer' => array_replace($defaults['footer'], $saved['footer'] ?? []),
        ]);
    }
}

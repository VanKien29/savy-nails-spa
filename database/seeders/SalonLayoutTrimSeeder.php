<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class SalonLayoutTrimSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::where('template', 'salon-home')->firstOrFail();
        $sections = collect($page->sections)->reject(fn ($section) => in_array($section['id'], ['visit', 'finishing'], true))->values()->all();
        if ($sections !== $page->sections) {
            $page->update(['sections' => $sections]);
        }
    }
}

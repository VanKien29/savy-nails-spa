<?php

use App\Models\Lead;
use App\Models\SalonCategory;
use App\Models\SalonService;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

// CLI-only fixture, never a web endpoint. Password arrives on stdin and is never echoed.
if (PHP_SAPI !== 'cli') {
    exit(1);
}
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! $app->environment(['local', 'testing'])) {
    exit(1);
}
$data = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
if (! preg_match('/^browser-[a-f0-9-]+@example\.invalid$/', $data['email'] ?? '')) {
    exit(1);
}
if ($data['action'] === 'cleanupMenu') {
    if (! preg_match('/^browser-menu-[a-f0-9-]+$/', $data['category_slug'] ?? '')) {
        exit(1);
    }
    if ($category = SalonCategory::where('slug', $data['category_slug'])->first()) {
        SalonService::where('category_id', $category->id)->get()->each->delete();
        $category->delete();
    }
} elseif ($data['action'] === 'create') {
    $user = new User(['name' => 'Browser test', 'email' => $data['email'], 'password' => $data['password']]);
    $user->is_admin = true;
    $user->save();
} else {
    User::where('email', $data['email'])->delete();
    Lead::where('email', $data['email'])->delete();
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StarterCheck extends Command
{
    protected $signature = 'starter:check {--production : Enforce production configuration}';

    protected $description = 'Check runtime, extensions, database and deployment prerequisites without revealing secrets';

    public function handle(): int
    {
        $checks = ['PHP stable >=8.5.12 <8.6' => PHP_VERSION_ID >= 80512 && PHP_VERSION_ID < 80600 && ! preg_match('/RC|alpha|beta/i', PHP_VERSION), 'Application key exists' => (bool) config('app.key'), 'Built assets' => is_file(public_path('build/manifest.json')), 'storage writable' => is_writable(storage_path()), 'bootstrap/cache writable' => is_writable(base_path('bootstrap/cache'))];
        foreach (['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pcre', 'pdo', 'session', 'tokenizer', 'xml', 'intl', 'gd', 'exif', 'zip'] as $extension) {
            $checks['Extension '.$extension] = extension_loaded($extension);
        }
        try {
            DB::connection()->getPdo();
            $checks['Database connection'] = true;
        } catch (\Throwable) {
            $checks['Database connection'] = false;
        }
        if ($this->option('production')) {
            $origin = parse_url(config('starter.site_url'));
            $canonical = ($origin['scheme'] ?? '') === 'https' && ! empty($origin['host'])
                && ! in_array($origin['host'], ['localhost', '127.0.0.1', '::1'], true)
                && empty($origin['user']) && empty($origin['pass']) && empty($origin['query']) && empty($origin['fragment']) && in_array($origin['path'] ?? '', ['', '/'], true);
            $checks += ['Production environment' => app()->environment('production'), 'Debug disabled' => ! config('app.debug'), 'Preview disabled' => ! config('starter.preview'), 'Secure session cookies' => (bool) config('session.secure'), 'MySQL/MariaDB driver' => config('database.default') === 'mysql', 'Canonical HTTPS origin' => $canonical, 'Showcase disabled' => ! config('starter.showcase')];
        }
        foreach ($checks as $name => $pass) {
            $this->line(($pass ? 'PASS ' : 'FAIL ').$name);
        }
        $this->comment('This checks CLI only. Independently verify the web SAPI patch version through your hosting control panel.');

        return in_array(false, $checks, true) ? self::FAILURE : self::SUCCESS;
    }
}

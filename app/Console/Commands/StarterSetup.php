<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class StarterSetup extends Command
{
    protected $signature = 'starter:setup {--name= : Brand/site name} {--domain= : Production HTTPS origin} {--admin-email= : Create a new authorized admin, password is prompted securely} {--demo : Seed labeled demo content only in local/testing}';

    protected $description = 'Initialize a client without replacing existing settings, pages or accounts';

    public function handle(): int
    {
        if ($this->option('demo') && ! app()->environment(['local', 'testing'])) {
            $this->error('Demo is restricted to local/testing.');

            return self::FAILURE;
        }
        $name = $this->option('name') ?: 'Website của bạn';
        Validator::make(['name' => $name], ['name' => 'required|string|max:100'])->validate();
        if ($domain = $this->option('domain')) {
            Validator::make(['domain' => $domain], ['domain' => ['url:https', 'regex:~^https://[a-zA-Z0-9.-]+(?::[0-9]+)?/?$~']])->validate();
            $env = file_get_contents(base_path('.env'));
            if (! preg_match('/^SITE_URL=/m', $env)) {
                file_put_contents(base_path('.env'), $env."\nSITE_URL=".rtrim($domain, '/')."\n");
                $this->info('SITE_URL added. Run optimize:clear to reload configuration.');
            } else {
                $this->warn('Existing SITE_URL preserved. Edit .env intentionally if it needs to change.');
            }
        }
        if ($this->option('demo')) {
            $this->call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
        } else {
            if (! SiteSetting::find(1)) {
                app(SiteSettingsService::class)->update(array_replace(SiteSettingsService::defaults(), ['site_name' => $name, 'company_name' => $name, 'default_title' => $name]));
            }
            Page::firstOrCreate(['slug' => 'trang-chu'], ['title' => $name, 'status' => 'draft', 'is_home' => true, 'template' => 'sections', 'sections' => []]);
        }
        if ($email = $this->option('admin-email')) {
            Validator::make(['email' => $email], ['email' => 'required|email|max:160'])->validate();
            if (User::where('email', $email)->exists()) {
                $this->warn('Existing user preserved; no implicit privilege escalation or password reset.');
            } else {
                $password = $this->secret('New admin password (at least 14 characters)');
                Validator::make(['password' => $password], ['password' => 'required|string|min:14'])->validate();
                $user = new User(['name' => 'Administrator', 'email' => $email, 'password' => $password]);
                $user->is_admin = true;
                $user->save();
                $this->info('Authorized admin created. Password is not printed or saved in configuration.');
            }
        }
        $this->info('Setup finished. Configure feature flags, brand and navigation in /admin. Existing records preserved.');

        return self::SUCCESS;
    }
}

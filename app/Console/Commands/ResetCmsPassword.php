<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ResetCmsPassword extends Command
{
    protected $signature = 'cms:reset-password {email : Email của tài khoản quản trị} {--generate : Tạo mật khẩu ngẫu nhiên và hiển thị một lần}';

    protected $description = 'Đặt lại mật khẩu CMS bằng quyền truy cập máy chủ';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->where('is_admin', true)->first();
        if (! $user) {
            $this->error('Không tìm thấy tài khoản quản trị.');

            return self::FAILURE;
        }
        $password = $this->option('generate') ? 'K'.Str::random(20).'a9!' : $this->secret('Mật khẩu mới');
        Validator::make(['password' => $password], ['password' => ['required', Password::min(14)->mixedCase()->numbers()->symbols()]])->validate();
        $user->password = $password;
        $user->save();
        $this->info('Đã đặt lại mật khẩu và thu hồi phiên đăng nhập cũ.');
        if ($this->option('generate')) {
            $this->line('Password: '.$password);
        }

        return self::SUCCESS;
    }
}

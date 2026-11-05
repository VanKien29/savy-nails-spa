<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreateCmsAdmin extends Command
{
    protected $signature = 'cms:create-admin {--email= : Email đăng nhập; bỏ trống để tạo ngẫu nhiên} {--generate : Tạo mật khẩu ngẫu nhiên và chỉ hiển thị một lần}';

    protected $description = 'Tạo quản trị viên CMS; không thay đổi tài khoản đã có';

    public function handle(): int
    {
        $email = $this->option('email') ?: 'admin.'.Str::lower(Str::random(8)).'@savy.local';
        Validator::make(['email' => $email], ['email' => 'required|email|max:160|unique:users,email'])->validate();
        $password = $this->option('generate') ? Str::password(24) : $this->secret('Mật khẩu mới');
        Validator::make(['password' => $password], ['password' => ['required', Password::min(14)->mixedCase()->numbers()->symbols()]])->validate();
        $user = new User(['name' => 'Savy Administrator', 'email' => $email, 'password' => $password]);
        $user->is_admin = true;
        $user->is_active = true;
        $user->save();
        $this->info('Đã tạo quản trị viên CMS.');
        $this->line('Email: '.$email);
        if ($this->option('generate')) {
            $this->line('Password: '.$password);
            $this->warn('Lưu mật khẩu vào trình quản lý mật khẩu. Mật khẩu không được lưu dạng rõ trong dự án.');
        }

        return self::SUCCESS;
    }
}

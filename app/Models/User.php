<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable implements FilamentUser
{
    protected $attributes = ['is_active' => true];

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_admin && (bool) $this->is_active;
    }

    public function canBeDeletedBy(self $actor): bool
    {
        return $actor->is_admin && $actor->is_active && $this->is_admin && ! $this->is($actor)
            && (! $this->is_active || self::whereKeyNot($this->id)->where('is_admin', true)->where('is_active', true)->exists());
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'app_authentication_secret',
        'app_authentication_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $user): void {
            if ($user->getOriginal('is_admin') && $user->getOriginal('is_active')
                && ! self::whereKeyNot($user->id)->where('is_admin', true)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['user' => 'Phải giữ ít nhất một quản trị viên đang hoạt động.']);
            }
        });
        static::deleted(function (self $user): void {
            if (config('session.driver') === 'database') {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
            $broker = config('auth.defaults.passwords', 'users');
            DB::table(config('auth.passwords.'.$broker.'.table', 'password_reset_tokens'))->where('email', $user->email)->delete();
        });
        static::saving(function (self $user) {
            if ($user->exists && $user->getOriginal('is_admin') && $user->getOriginal('is_active')
                && (! $user->is_admin || ! $user->is_active)
                && ! self::whereKeyNot($user->id)->where('is_admin', true)->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['data.is_active' => 'Phải giữ ít nhất một quản trị viên đang hoạt động.']);
            }
        });
        static::updated(function (self $user) {
            if ($user->wasChanged(['password', 'is_active', 'is_admin']) && config('session.driver') === 'database') {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        });
    }
}

<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Services\CmsAccess;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Schemas\Schema;
use Filament\Tables\Columns as C;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class UserResource extends StarterResource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'tài khoản';

    protected static ?string $pluralModelLabel = 'Tài khoản Admin';

    protected static string|\UnitEnum|null $navigationGroup = 'Hệ thống';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 10;

    public static function canDelete(Model $record): bool
    {
        return CmsAccess::allows('users') && $record instanceof User && $record->canBeDeletedBy(auth()->user());
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            F\TextInput::make('name')->label('Tên')->required()->maxLength(120),
            F\TextInput::make('email')->label('Email đăng nhập')->email()->required()->maxLength(160)->unique(ignoreRecord: true),
            F\TextInput::make('password')->label('Mật khẩu mới')->password()->revealable()->autocomplete('new-password')->rules([Password::min(14)->mixedCase()->numbers()->symbols()])->required(fn (string $operation) => $operation === 'create')->dehydrated(fn ($state) => filled($state))->helperText('Ít nhất 14 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt. Để trống khi sửa để giữ mật khẩu.'),
            F\Toggle::make('is_active')->label('Cho phép đăng nhập')->default(true),
        ]);
    }

    public static function persist(array $data, ?User $record = null): User
    {
        abort_unless(CmsAccess::allows('users'), 403);

        return DB::transaction(function () use ($data, $record) {
            User::where('is_admin', true)->orderBy('id')->lockForUpdate()->get();
            $record ??= new User;
            $record->forceFill(collect($data)->only(['name', 'email', 'password', 'is_active'])->all());
            $record->is_admin = true;
            $record->save();

            return $record;
        });
    }

    public static function deleteAccount(User $record): bool
    {
        abort_unless(CmsAccess::allows('users'), 403);

        return DB::transaction(function () use ($record): bool {
            $admins = User::where('is_admin', true)->orderBy('id')->lockForUpdate()->get();
            $actor = $admins->firstWhere('id', auth()->id());
            $target = $admins->firstWhere('id', $record->id);
            abort_unless($actor && $target && $target->canBeDeletedBy($actor), 403);

            return (bool) $target->delete();
        });
    }

    public static function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn ($query) => $query->where('is_admin', true))->columns([
            C\TextColumn::make('name')->label('Tên')->searchable(), C\TextColumn::make('email')->searchable(),
            C\IconColumn::make('is_active')->label('Hoạt động')->boolean(),
        ])->recordActions([
            EditAction::make()->mutateRecordDataUsing(fn (array $data) => array_replace($data, ['password' => '']))->using(fn (User $record, array $data) => self::persist($data, $record)),
            DeleteAction::make()->label('Xóa')->requiresConfirmation()
                ->modalHeading(fn (User $record) => 'Xóa tài khoản '.$record->name.'?')
                ->modalDescription('Tài khoản sẽ bị xóa vĩnh viễn và không thể đăng nhập lại. Nội dung website và nhật ký đã ghi vẫn được giữ nguyên.')
                ->modalSubmitActionLabel('Xóa tài khoản')->successNotificationTitle('Đã xóa tài khoản')
                ->using(fn (User $record) => self::deleteAccount($record)),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => UserResource\Pages\ManageUsers::route('/')];
    }
}

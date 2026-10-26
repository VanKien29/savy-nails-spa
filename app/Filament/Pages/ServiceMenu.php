<?php

namespace App\Filament\Pages;

use App\Models\SalonCategory;
use App\Models\SalonService;
use App\Services\CmsAccess;
use Filament\Actions\Action;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class ServiceMenu extends Page
{
    protected static ?string $title = 'Menu dịch vụ';

    protected string $view = 'filament.pages.service-menu';

    #[Url(as: 'category')]
    public ?int $categoryId = null;

    public bool $showHidden = false;

    public static function canAccess(): bool
    {
        return CmsAccess::allows('content');
    }

    #[Computed]
    public function menuCategories(): Collection
    {
        return SalonCategory::withCount('services')->orderBy('sort_order')->orderBy('id')->get();
    }

    #[Computed]
    public function selectedCategory(): ?SalonCategory
    {
        return $this->menuCategories->firstWhere('id', $this->categoryId) ?? $this->menuCategories->first();
    }

    #[Computed]
    public function groupedServices(): Collection
    {
        return SalonService::where('category_id', $this->selectedCategory?->id)
            ->when(! $this->showHidden, fn ($query) => $query->where('active', true))
            ->orderBy('sort_order')->orderBy('id')->get()->groupBy('group');
    }

    #[Computed]
    public function hiddenCount(): int
    {
        return SalonService::where('category_id', $this->selectedCategory?->id)->where('active', false)->count();
    }

    public function createServiceAction(): Action
    {
        return Action::make('createService')->label('Thêm dịch vụ')->icon('heroicon-o-plus')
            ->authorize(fn () => static::canAccess())->modalHeading('Thêm dịch vụ')->modalSubmitActionLabel('Thêm vào menu')->modalWidth('3xl')
            ->fillForm(fn () => ['category_id' => $this->selectedCategory?->id, 'active' => true, 'price_from' => false])
            ->schema($this->serviceFields())->action(function (array $data): void {
                abort_unless(static::canAccess(), 403);
                DB::transaction(function () use ($data): void {
                    SalonCategory::whereKey($data['category_id'])->lockForUpdate()->firstOrFail();
                    $values = $this->serviceData($data);
                    $values['sort_order'] = (int) SalonService::where('category_id', $data['category_id'])->max('sort_order') + 1;
                    SalonService::create($values);
                    $this->categoryId = (int) $data['category_id'];
                });
                $this->saved('Đã thêm dịch vụ');
            });
    }

    public function editServiceAction(): Action
    {
        return Action::make('editService')->label('Sửa')->authorize(fn () => static::canAccess())
            ->record(fn (array $arguments) => SalonService::findOrFail($arguments['id'] ?? 0))
            ->modalHeading(fn (SalonService $record) => 'Sửa '.$record->name)->modalSubmitActionLabel('Lưu thay đổi')->modalWidth('3xl')
            ->fillForm(fn (SalonService $record) => array_replace($record->toArray(), ['included_steps' => implode("\n", $record->includes ?? [])]))
            ->schema($this->serviceFields())
            ->extraModalFooterActions(fn (SalonService $record) => [$this->deleteServiceAction()->arguments(['id' => $record->id])])
            ->action(function (SalonService $record, array $data): void {
                abort_unless(static::canAccess(), 403);
                DB::transaction(function () use ($record, $data): void {
                    SalonCategory::whereKey($data['category_id'])->lockForUpdate()->firstOrFail();
                    SalonService::whereKey($record->id)->lockForUpdate()->firstOrFail()->update($this->serviceData($data));
                    $this->categoryId = (int) $data['category_id'];
                });
                $this->saved('Đã cập nhật dịch vụ trên website');
            });
    }

    public function deleteServiceAction(): Action
    {
        return Action::make('deleteService')->label('Xóa dịch vụ')->color('danger')->authorize(fn () => static::canAccess())
            ->record(fn (array $arguments) => SalonService::findOrFail($arguments['id'] ?? 0))
            ->requiresConfirmation()->modalHeading('Xóa dịch vụ này?')->modalDescription('Dịch vụ sẽ được gỡ khỏi menu. Các yêu cầu liên hệ đã nhận được giữ nguyên.')
            ->modalSubmitActionLabel('Xóa dịch vụ')->cancelParentActions()
            ->action(function (SalonService $record): void {
                abort_unless(static::canAccess(), 403);
                $record->delete();
                $this->saved('Đã xóa dịch vụ');
            });
    }

    private function serviceFields(): array
    {
        return [
            F\TextInput::make('name')->label('Tên dịch vụ')->required()->maxLength(200)->columnSpanFull(),
            F\Select::make('category_id')->label('Danh mục')->options(fn () => SalonCategory::orderBy('sort_order')->pluck('name', 'id'))->required()->live(),
            F\TextInput::make('group')->label('Nhóm nhỏ trong danh mục')->maxLength(160)
                ->datalist(fn (\Filament\Schemas\Components\Utilities\Get $get) => SalonService::where('category_id', $get('category_id'))->whereNotNull('group')->distinct()->pluck('group')->all())
                ->placeholder('Ví dụ: Nail Enhancements')->helperText('Để trống nếu không cần chia nhóm.'),
            Section::make('Giá trên menu')->schema([
                F\TextInput::make('price')->label('Giá thường / Full Set')->prefix('$')->numeric()->minValue(0)->maxValue(999999.99),
                F\TextInput::make('fill_price')->label('Fill-in (nếu có)')->prefix('$')->numeric()->minValue(0)->maxValue(999999.99),
                F\TextInput::make('gel_price')->label('Gel Polish (nếu có)')->prefix('$')->numeric()->minValue(0)->maxValue(999999.99),
                F\Toggle::make('price_from')->label('Thêm dấu + sau giá')->default(false),
                F\TextInput::make('price_note')->label('Giá bằng chữ / ghi chú')->maxLength(255)->placeholder('Ví dụ: 50% of Full Set Price')->columnSpanFull(),
            ])->columns(3)->columnSpanFull(),
            F\Textarea::make('description')->label('Mô tả dịch vụ')->rows(3)->maxLength(5000)->columnSpanFull(),
            Section::make('Các bước chăm sóc & thời lượng')->description('Dành cho gói pedicure, làm mày và dịch vụ có nội dung chi tiết.')
                ->collapsible()->collapsed()->schema([
                    F\TextInput::make('includes_label')->label('Tiêu đề các bước')->placeholder('Includes:')->maxLength(160),
                    F\Textarea::make('included_steps')->label('Các bước chăm sóc')->rows(7)->maxLength(7500)
                        ->helperText('Mỗi dòng là một bước. Tối đa 24 bước, mỗi bước 300 ký tự.')
                        ->rules([fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                            $lines = $this->stepLines($value);
                            if (count($lines) > 24 || collect($lines)->contains(fn ($line) => mb_strlen($line) > 300)) {
                                $fail('Nhập tối đa 24 bước, mỗi bước không quá 300 ký tự.');
                            }
                        }]),
                    F\TextInput::make('duration_label')->label('Thời lượng / ghi chú thời gian')->maxLength(255)->placeholder('Ví dụ: Time: 2 Hours'),
                ])->columnSpanFull(),
            F\Toggle::make('active')->label('Hiển thị dịch vụ trên website')->default(true)->columnSpanFull(),
        ];
    }

    private function stepLines(?string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $value ?? '')), fn ($line) => $line !== ''));
    }

    private function serviceData(array $data): array
    {
        return array_replace(collect($data)->only(['name', 'category_id', 'group', 'description', 'price', 'fill_price', 'gel_price', 'price_from', 'price_note', 'includes_label', 'duration_label', 'active'])->all(), [
            'group' => filled($data['group'] ?? null) ? trim($data['group']) : null,
            'includes' => $this->stepLines($data['included_steps'] ?? null),
        ]);
    }

    public function createCategoryAction(): Action
    {
        return Action::make('createCategory')->label('Thêm danh mục')->icon('heroicon-o-plus')->color('gray')->authorize(fn () => static::canAccess())
            ->modalHeading('Thêm danh mục dịch vụ')->modalSubmitActionLabel('Thêm danh mục')->schema($this->categoryFields())
            ->action(function (array $data): void {
                abort_unless(static::canAccess(), 403);
                $base = rtrim(substr(Str::slug($data['name']) ?: 'category', 0, 80), '-');
                $slug = $base;
                while (SalonCategory::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.Str::lower(Str::random(6));
                }
                $category = SalonCategory::create(['name' => $data['name'], 'description' => $data['description'] ?? '', 'slug' => $slug, 'sort_order' => (int) SalonCategory::max('sort_order') + 1]);
                $this->categoryId = $category->id;
                $this->saved('Đã thêm danh mục');
            });
    }

    public function editCategoryAction(): Action
    {
        return Action::make('editCategory')->label('Sửa danh mục')->color('gray')->authorize(fn () => static::canAccess())
            ->record(fn (array $arguments) => SalonCategory::findOrFail($arguments['id'] ?? 0))
            ->modalHeading('Sửa danh mục')->modalSubmitActionLabel('Lưu thay đổi')->schema($this->categoryFields())->fillForm(fn (SalonCategory $record) => $record->toArray())
            ->extraModalFooterActions(fn (SalonCategory $record) => SalonService::where('category_id', $record->id)->exists() ? [] : [$this->deleteCategoryAction()->arguments(['id' => $record->id])])
            ->action(function (SalonCategory $record, array $data): void {
                abort_unless(static::canAccess(), 403);
                $record->update(['name' => $data['name'], 'description' => $data['description'] ?? '']);
                $this->saved('Đã cập nhật danh mục');
            });
    }

    private function categoryFields(): array
    {
        return [
            F\TextInput::make('name')->label('Tên danh mục')->required()->maxLength(160),
            F\Textarea::make('description')->label('Mô tả ngắn trên trang Home')->rows(3)->maxLength(2000),
        ];
    }

    public function deleteCategoryAction(): Action
    {
        return Action::make('deleteCategory')->label('Xóa danh mục')->color('danger')->authorize(fn () => static::canAccess())
            ->record(fn (array $arguments) => SalonCategory::findOrFail($arguments['id'] ?? 0))->requiresConfirmation()
            ->modalHeading('Xóa danh mục trống này?')->modalDescription('Chỉ có thể xóa danh mục không còn dịch vụ, kể cả dịch vụ đang ẩn.')->cancelParentActions()
            ->action(function (SalonCategory $record): void {
                abort_unless(static::canAccess(), 403);
                DB::transaction(function () use ($record): void {
                    $category = SalonCategory::whereKey($record->id)->lockForUpdate()->firstOrFail();
                    if (SalonService::where('category_id', $category->id)->exists()) {
                        throw ValidationException::withMessages(['category' => 'Chuyển hoặc xóa các dịch vụ trong danh mục trước khi xóa danh mục.']);
                    }
                    $category->delete();
                });
                $this->categoryId = null;
                $this->saved('Đã xóa danh mục');
            });
    }

    public function toggleService(int $id): void
    {
        abort_unless(static::canAccess(), 403);
        DB::transaction(function () use ($id): void {
            $service = SalonService::whereKey($id)->lockForUpdate()->firstOrFail();
            $service->update(['active' => ! $service->active]);
        });
        $this->saved('Đã cập nhật hiển thị trên website');
    }

    public function moveService(int $id, string $direction): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        DB::transaction(function () use ($id, $direction): void {
            $record = SalonService::findOrFail($id);
            SalonCategory::whereKey($record->category_id)->lockForUpdate()->firstOrFail();
            $items = SalonService::where('category_id', $record->category_id)->orderBy('sort_order')->orderBy('id')->lockForUpdate()->get();
            $group = $items->filter(fn ($service) => $service->group === $record->group && ($this->showHidden || $service->active))->values();
            $position = $group->search(fn ($service) => $service->id === $id);
            if ($position === false || ! isset($group[$position + ($direction === 'up' ? -1 : 1)])) {
                return;
            }
            $neighbor = $group[$position + ($direction === 'up' ? -1 : 1)];
            $first = $items->search(fn ($service) => $service->id === $id);
            $second = $items->search(fn ($service) => $service->id === $neighbor->id);
            [$items[$first], $items[$second]] = [$items[$second], $items[$first]];
            foreach ($items as $index => $service) {
                $service->update(['sort_order' => $index]);
            }
        });
        $this->saved('Đã đổi thứ tự dịch vụ');
    }

    public function moveCategory(int $id, string $direction): void
    {
        abort_unless(static::canAccess(), 403);
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        DB::transaction(function () use ($id, $direction): void {
            $items = SalonCategory::orderBy('sort_order')->orderBy('id')->lockForUpdate()->get();
            $position = $items->search(fn ($category) => $category->id === $id);
            $neighbor = $position + ($direction === 'up' ? -1 : 1);
            if ($position === false || ! isset($items[$neighbor])) {
                return;
            }
            [$items[$position], $items[$neighbor]] = [$items[$neighbor], $items[$position]];
            foreach ($items as $index => $category) {
                $category->update(['sort_order' => $index]);
            }
        });
        $this->saved('Đã đổi thứ tự danh mục');
    }

    private function saved(string $message): void
    {
        unset($this->menuCategories, $this->selectedCategory, $this->groupedServices, $this->hiddenCount);
        Notification::make()->title($message)->success()->send();
    }
}

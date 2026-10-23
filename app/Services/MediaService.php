<?php

namespace App\Services;

use App\Jobs\ProcessMedia;
use App\Models\Media;
use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaService
{
    public function upload(UploadedFile $file, string $alt = ''): Media
    {
        if (config('starter.preview')) {
            throw ValidationException::withMessages(['file' => 'Preview không hỗ trợ upload.']);
        }
        $mime = $file->getMimeType();
        $info = @getimagesize($file->getRealPath());
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/avif'], true) || ! $info || $file->getSize() > config('starter.media.max_kb') * 1024 || config('starter.media.max_pixels') < $info[0] * $info[1]) {
            throw ValidationException::withMessages(['file' => 'Chỉ nhận JPG/PNG/WebP/AVIF, tối đa 8 MB và 25 megapixel. SVG và GIF upload không được hỗ trợ.']);
        }
        if ($mime === 'image/webp' && ! function_exists('imagecreatefromwebp')) {
            throw ValidationException::withMessages(['file' => 'Máy chủ không có WebP decoder; hãy gửi PNG/JPG.']);
        }
        if ($mime === 'image/avif' && ! function_exists('imagecreatefromavif')) {
            throw ValidationException::withMessages(['file' => 'Máy chủ không có AVIF decoder; hãy gửi PNG/JPG/WebP.']);
        }
        $path = $file->storeAs('originals', Str::uuid().'.'.match ($mime) {
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', default => 'avif'
        }, 'local');
        $media = Media::create(['name' => mb_substr(basename($file->getClientOriginalName()), 0, 200), 'alt' => mb_substr($alt, 0, 500), 'mime' => $mime, 'disk' => config('starter.media.disk'), 'original_path' => $path, 'width' => $info[0], 'height' => $info[1], 'size' => $file->getSize()]);
        if (config('starter.media.queue')) {
            ProcessMedia::dispatch($media->id);
        } else {
            $this->process($media);
        }

        return $media->refresh();
    }

    public function process(Media $media): void
    {
        $path = Storage::disk('local')->path($media->original_path);
        $image = match ($media->mime) {
            'image/jpeg' => imagecreatefromjpeg($path), 'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path), 'image/avif' => imagecreatefromavif($path),
        };
        if (! $image) {
            throw new \RuntimeException('Image decode failed');
        }
        if ($media->mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $orientation = (@exif_read_data($path)['Orientation'] ?? 1);
            if (in_array($orientation, [2, 4, 5, 7])) {
                imageflip($image, IMG_FLIP_HORIZONTAL);
            }
            $rotation = match ($orientation) {
                3, 4 => 180, 5, 6 => -90, 7, 8 => 90, default => 0
            };
            if ($rotation) {
                $image = imagerotate($image, $rotation, 0);
            }
        }
        $width = imagesx($image);
        $height = imagesy($image);
        $variants = [];
        $widths = collect(config('starter.media.widths'))->map(fn ($w) => min($w, $width))->unique();
        foreach ($widths as $target) {
            $targetHeight = max(1, (int) round($height * $target / $width));
            $resized = imagecreatetruecolor($target, $targetHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagefilledrectangle($resized, 0, 0, $target, $targetHeight, imagecolorallocatealpha($resized, 0, 0, 0, 127));
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $target, $targetHeight, $width, $height);
            $format = function_exists('imagewebp') && (imagetypes() & IMG_WEBP) ? 'webp' : 'png';
            ob_start();
            $format === 'webp' ? imagewebp($resized, null, 82) : imagepng($resized);
            $bytes = ob_get_clean();
            $destination = 'media/'.$media->id.'/'.$target.'.'.$format;
            Storage::disk($media->disk)->put($destination, $bytes);
            $variants[] = ['path' => $destination, 'width' => $target, 'height' => $targetHeight, 'format' => $format, 'size' => strlen($bytes)];
        }
        $media->update(['variants' => $variants, 'width' => $width, 'height' => $height, 'status' => 'ready']);
    }

    public function inUse(Media $media): bool
    {
        if (Page::where('hero_media_id', $media->id)->orWhere('og_media_id', $media->id)->exists()) {
            return true;
        }
        foreach (Page::cursor() as $page) {
            foreach ($page->sections ?? [] as $section) {
                if ((int) ($section['media_id'] ?? 0) === $media->id) {
                    return true;
                }
                foreach ($section['items'] ?? [] as $item) {
                    if ((int) ($item['media_id'] ?? 0) === $media->id) {
                        return true;
                    }
                }
            }
        }
        foreach (['logo_id', 'mobile_logo_id', 'favicon_id', 'og_media_id'] as $key) {
            if ((int) app(SiteSettingsService::class)->get($key) === $media->id) {
                return true;
            }
        }

        return false;
    }

    public function delete(Media $media): void
    {
        if ($this->inUse($media)) {
            throw ValidationException::withMessages(['media' => 'Ảnh đang được sử dụng. Hãy gỡ liên kết trước khi xóa.']);
        }
        Storage::disk($media->disk)->deleteDirectory('media/'.$media->id);
        Storage::disk('local')->delete($media->original_path);
        $media->delete();
    }
}

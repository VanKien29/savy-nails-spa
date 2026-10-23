<?php

namespace App\Jobs;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMedia implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public int $mediaId) {}

    public function handle(MediaService $service): void
    {
        if ($media = Media::find($this->mediaId)) {
            $service->process($media);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Media::find($this->mediaId)?->update(['status' => 'failed']);
    }
}

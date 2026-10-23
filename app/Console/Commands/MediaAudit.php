<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MediaAudit extends Command
{
    protected $signature = 'starter:media-audit';

    protected $description = 'Check image sizes, variants and missing original/derived files without changing them';

    public function handle(): int
    {
        $errors = 0;
        foreach (Media::cursor() as $media) {
            if ($media->status !== 'ready' || ! Storage::disk('local')->exists($media->original_path)) {
                $this->error('Media #'.$media->id.': not ready or missing original');
                $errors++;
            }
            foreach ($media->variants ?? [] as $variant) {
                if (! Storage::disk($media->disk)->exists($variant['path'])) {
                    $this->error('Missing variant #'.$media->id);
                    $errors++;
                }
            }
            $total = collect($media->variants ?? [])->sum('size');
            $this->line('#'.$media->id.' original='.$media->size.' variants='.$total.' bytes; '.$media->width.'×'.$media->height);
            if ($media->size > 2 * 1024 * 1024) {
                $this->warn('Original #'.$media->id.' exceeds 2 MB; use responsive variants.');
            }
        }

        return $errors ? self::FAILURE : self::SUCCESS;
    }
}

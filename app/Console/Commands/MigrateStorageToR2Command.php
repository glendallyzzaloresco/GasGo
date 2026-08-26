<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToR2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:sync-r2 {--force : Overwrite existing files on R2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize and upload all local storage files directly to Cloudflare R2';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $localStoragePath = storage_path('app/public');

        if (! File::isDirectory($localStoragePath)) {
            $this->warn("Local storage directory not found: {$localStoragePath}");
            return self::FAILURE;
        }

        $r2Disk = Storage::disk('s3');
        $files = File::allFiles($localStoragePath);

        $this->info('Starting sync of ' . count($files) . ' files to Cloudflare R2...');

        $uploadedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($files as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());

            if (! $this->option('force') && $r2Disk->exists($relativePath)) {
                $this->line("<comment>[EXISTS]</comment> {$relativePath}");
                $skippedCount++;
                continue;
            }

            try {
                $mimeType = File::mimeType($file->getRealPath()) ?: 'application/octet-stream';
                $stream = fopen($file->getRealPath(), 'r+');

                $uploaded = $r2Disk->put($relativePath, $stream, [
                    'visibility' => 'public',
                    'mimetype'   => $mimeType,
                ]);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                if ($uploaded) {
                    $this->line("<info>[UPLOADED]</info> {$relativePath} -> " . $r2Disk->url($relativePath));
                    $uploadedCount++;
                } else {
                    $this->error("[FAILED] Could not upload: {$relativePath}");
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                $this->error("[ERROR] {$relativePath}: " . $e->getMessage());
                $failedCount++;
            }
        }

        $this->newLine();
        $this->info("Cloudflare R2 Sync Completed!");
        $this->info("Uploaded: {$uploadedCount}, Skipped (already on R2): {$skippedCount}, Failed: {$failedCount}");

        return self::SUCCESS;
    }
}

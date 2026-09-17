<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToSupabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:sync-supabase 
                            {--force : Overwrite existing files in Supabase Storage}
                            {--disk= : Storage disk to upload to (defaults to supabase or s3)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize and upload all local storage files directly to Supabase Storage';

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

        // Determine target disk
        $diskName = $this->option('disk');
        if (! $diskName) {
            $default = config('filesystems.default');
            if (in_array($default, ['supabase', 's3'], true)) {
                $diskName = $default;
            } else {
                $diskName = config('filesystems.disks.supabase.endpoint') ? 'supabase' : 's3';
            }
        }

        $targetDisk = Storage::disk($diskName);
        $files = File::allFiles($localStoragePath);

        $this->info("Starting sync of " . count($files) . " files to Supabase Storage (Disk: [{$diskName}])...");

        $uploadedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($files as $file) {
            $relativePath = str_replace('\\', '/', $file->getRelativePathname());

            // Skip gitignore or hidden files
            if ($file->getFilename() === '.gitignore') {
                continue;
            }

            if (! $this->option('force')) {
                try {
                    if ($targetDisk->exists($relativePath)) {
                        $this->line("<comment>[EXISTS]</comment> {$relativePath}");
                        $skippedCount++;
                        continue;
                    }
                } catch (\Throwable $e) {
                    // If exists check fails, attempt upload
                }
            }

            try {
                $mimeType = File::mimeType($file->getRealPath()) ?: 'application/octet-stream';
                $stream = fopen($file->getRealPath(), 'r+');

                // Note: Supabase S3 does not support standard S3 ACL headers (like 'visibility' => 'public').
                // Bucket visibility must be set to 'Public' in the Supabase Dashboard.
                $uploaded = $targetDisk->put($relativePath, $stream, [
                    'mimetype' => $mimeType,
                ]);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                if ($uploaded) {
                    $url = $targetDisk->url($relativePath);
                    $this->line("<info>[UPLOADED]</info> {$relativePath} -> {$url}");
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
        $this->info("Supabase Storage Sync Completed!");
        $this->info("Uploaded: {$uploadedCount}, Skipped: {$skippedCount}, Failed: {$failedCount}");

        return self::SUCCESS;
    }
}

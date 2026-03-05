<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

/**
 * FileUploadService
 * Handles file uploads from API and Telegram
 */
class FileUploadService
{
    /**
     * Upload file from HTTP request
     */
    public function upload(UploadedFile $file, string $directory = 'uploads', ?array $options = []): array
    {
        $options = array_merge([
            'resize' => false,
            'max_width' => 1200,
            'max_height' => 1200,
            'quality' => 85,
            'disk' => 'public',
        ], $options ?? []);

        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = $directory . '/' . $filename;

        // Handle image resizing if needed
        if ($options['resize'] && $this->isImage($file)) {
            $image = Image::make($file);

            // Resize if larger than max dimensions
            if ($image->width() > $options['max_width'] || $image->height() > $options['max_height']) {
                $image->resize($options['max_width'], $options['max_height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Save to storage
            $imageData = $image->encode($extension, $options['quality'])->__toString();
            Storage::disk($options['disk'])->put($path, $imageData);
        } else {
            // Store file directly
            $file->storeAs($directory, $filename, $options['disk']);
        }

        return [
            'path' => $path,
            'url' => Storage::disk($options['disk'])->url($path),
            'filename' => $filename,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Upload file from Telegram bot
     */
    public function uploadFromTelegram(string $fileId, string $directory = 'telegram', ?array $options = []): ?array
    {
        $options = array_merge([
            'disk' => 'public',
        ], $options ?? []);

        try {
            // Get file path from Telegram
            $botToken = config('services.telegram.bot_token');
            $response = Http::get("https://api.telegram.org/bot{$botToken}/getFile", [
                'file_id' => $fileId,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            if (!isset($data['result']['file_path'])) {
                return null;
            }

            $filePath = $data['result']['file_path'];
            $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";

            // Download file
            $fileContent = Http::get($fileUrl)->body();

            // Generate filename
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $filename = Str::uuid() . '.' . $extension;
            $path = $directory . '/' . $filename;

            // Store file
            Storage::disk($options['disk'])->put($path, $fileContent);

            $size = Storage::disk($options['disk'])->size($path);

            return [
                'path' => $path,
                'url' => Storage::disk($options['disk'])->url($path),
                'filename' => $filename,
                'size' => $size,
                'telegram_file_id' => $fileId,
            ];

        } catch (\Exception $e) {
            \Log::error('Telegram file upload error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload base64 encoded image
     */
    public function uploadBase64(string $base64Data, string $directory = 'base64', ?array $options = []): array
    {
        $options = array_merge([
            'disk' => 'public',
            'quality' => 85,
        ], $options ?? []);

        // Remove data URI prefix if present
        if (str_contains($base64Data, 'base64,')) {
            $base64Data = explode('base64,', $base64Data)[1];
        }

        // Decode base64
        $imageData = base64_decode($base64Data);

        // Detect image type
        $f = finfo_open();
        $mimeType = finfo_buffer($f, $imageData, FILEINFO_MIME_TYPE);
        finfo_close($f);

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        // Generate filename
        $filename = Str::uuid() . '.' . $extension;
        $path = $directory . '/' . $filename;

        // Store file
        Storage::disk($options['disk'])->put($path, $imageData);

        return [
            'path' => $path,
            'url' => Storage::disk($options['disk'])->url($path),
            'filename' => $filename,
            'size' => strlen($imageData),
            'mime_type' => $mimeType,
        ];
    }

    /**
     * Delete file
     */
    public function delete(string $path, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Upload avatar/profile image with automatic cropping
     */
    public function uploadAvatar(UploadedFile $file, string $directory = 'avatars'): array
    {
        $this->validateImage($file);

        $image = Image::make($file);

        // Make square crop from center
        $size = min($image->width(), $image->height());
        $image->crop($size, $size);

        // Resize to standard avatar size
        $image->resize(300, 300);

        // Generate filename
        $filename = Str::uuid() . '.jpg';
        $path = $directory . '/' . $filename;

        // Save as JPEG
        $imageData = $image->encode('jpg', 90)->__toString();
        Storage::disk('public')->put($path, $imageData);

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $filename,
            'size' => strlen($imageData),
            'mime_type' => 'image/jpeg',
        ];
    }

    /**
     * Upload company logo with background removal (optional)
     */
    public function uploadLogo(UploadedFile $file, string $directory = 'logos'): array
    {
        $this->validateImage($file);

        $image = Image::make($file);

        // Resize maintaining aspect ratio, max 400x400
        $image->resize(400, 400, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Generate filename
        $filename = Str::uuid() . '.png';
        $path = $directory . '/' . $filename;

        // Save as PNG (supports transparency)
        $imageData = $image->encode('png', 90)->__toString();
        Storage::disk('public')->put($path, $imageData);

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $filename,
            'size' => strlen($imageData),
            'mime_type' => 'image/png',
        ];
    }

    /**
     * Validate file upload
     */
    protected function validateFile(UploadedFile $file): void
    {
        $maxSize = 10 * 1024 * 1024; // 10MB

        if ($file->getSize() > $maxSize) {
            throw new \Exception('Fayl hajmi juda katta. Maksimal: 10MB');
        }

        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \Exception('Fayl turi qo\'llab-quvvatlanmaydi');
        }
    }

    /**
     * Validate image file
     */
    protected function validateImage(UploadedFile $file): void
    {
        $maxSize = 5 * 1024 * 1024; // 5MB

        if ($file->getSize() > $maxSize) {
            throw new \Exception('Rasm hajmi juda katta. Maksimal: 5MB');
        }

        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \Exception('Faqat rasm fayllarini yuklash mumkin (JPG, PNG, GIF, WebP)');
        }
    }

    /**
     * Check if file is an image
     */
    protected function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    /**
     * Get file info
     */
    public function getFileInfo(string $path, string $disk = 'public'): ?array
    {
        if (!Storage::disk($disk)->exists($path)) {
            return null;
        }

        return [
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'size' => Storage::disk($disk)->size($path),
            'last_modified' => Storage::disk($disk)->lastModified($path),
        ];
    }
}

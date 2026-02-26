<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Validate a file against upload settings
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];
        $maxSizeBytes = max_upload_size() * 1024 * 1024;

        // Check file size
        $fileSizeBytes = $file->getSize();
        if ($fileSizeBytes > $maxSizeBytes) {
            $errors[] = "File size exceeds maximum allowed size of " . max_upload_size() . " MB.";
        }

        // Check file type
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedTypes = allowed_file_types();
        if (!in_array($extension, $allowedTypes)) {
            $allowedStr = implode(', ', $allowedTypes);
            $errors[] = "File type '{$extension}' is not allowed. Allowed types: {$allowedStr}";
        }

        return $errors;
    }

    /**
     * Check if file is valid
     */
    public function isValidFile(UploadedFile $file): bool
    {
        return empty($this->validateFile($file));
    }

    /**
     * Upload a file to storage
     */
    public function upload(UploadedFile $file, ?string $directory = null, ?string $filename = null): ?string
    {
        // Validate file first
        $errors = $this->validateFile($file);
        if (!empty($errors)) {
            Log::warning('File upload validation failed', ['errors' => $errors]);
            return null;
        }

        try {
            $directory = $directory ?? upload_path();
            $extension = $file->getClientOriginalExtension();
            $filename = $filename ?? $this->generateFilename($extension);

            $path = $file->storeAs($directory, $filename, 'public');

            return $path;
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload a base64 encoded image
     */
    public function uploadBase64(string $base64Data, ?string $directory = null, ?string $filename = null): ?string
    {
        $maxSizeBytes = max_upload_size() * 1024 * 1024;
        $allowedTypes = allowed_file_types();

        try {
            // Validate base64 image data
            if (!preg_match('/^data:image\/(jpeg|png|jpg|gif|webp);base64,/', $base64Data, $matches)) {
                Log::warning('Invalid base64 image format');
                return null;
            }

            $imageType = $matches[1];
            $extension = $imageType === 'jpeg' ? 'jpg' : $imageType;

            // Check if image type is allowed
            if (!in_array($extension, $allowedTypes)) {
                Log::warning("Image type '{$extension}' is not allowed");
                return null;
            }

            // Extract image data
            $imageData = substr($base64Data, strpos($base64Data, ',') + 1);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                Log::warning('Failed to decode base64 image');
                return null;
            }

            // Check file size
            $fileSize = strlen($imageData);
            if ($fileSize > $maxSizeBytes) {
                Log::warning("Image size ({$fileSize} bytes) exceeds maximum ({$maxSizeBytes} bytes)");
                return null;
            }

            $directory = $directory ?? upload_path();
            $filename = $filename ?? $this->generateFilename($extension);
            $path = $directory . '/' . $filename;

            Storage::disk('public')->put($path, $imageData);

            return $path;
        } catch (\Exception $e) {
            Log::error('Base64 image upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a file from storage
     */
    public function delete(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
            return true; // File doesn't exist, consider it deleted
        } catch (\Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the full URL for a file
     */
    public function getUrl(string $path): string
    {
        $publicUrl = storage_public_url();
        return asset(ltrim($publicUrl, '/') . '/' . $path);
    }

    /**
     * Check if a file exists
     */
    public function exists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }

    /**
     * Get file size in bytes
     */
    public function getFileSize(string $path): int
    {
        if ($this->exists($path)) {
            return Storage::disk('public')->size($path);
        }
        return 0;
    }

    /**
     * Get human-readable file size
     */
    public function getHumanFileSize(string $path): string
    {
        $bytes = $this->getFileSize($path);
        return $this->formatBytes($bytes);
    }

    /**
     * Format bytes to human-readable format
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get validation rules for file uploads based on settings
     */
    public function getValidationRules(string $fieldName = 'file', bool $required = true): array
    {
        $maxSizeKb = max_upload_size() * 1024; // Convert MB to KB for Laravel validation

        $rules = [];

        if ($required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        $rules[] = 'file';
        $rules[] = 'max:' . $maxSizeKb;
        $rules[] = 'mimes:' . implode(',', allowed_file_types());

        return [$fieldName => $rules];
    }

    /**
     * Generate a unique filename
     */
    protected function generateFilename(string $extension): string
    {
        return Str::uuid() . '_' . time() . '.' . $extension;
    }
}


<?php

namespace App\Services;

use  Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{


    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary(config('cloudinary.cloud_url'));
    }
    public function upload(UploadedFile $file, array $options = []): array
    {
        try {
            Log::info("Start upload to Cloudinary");
            Log::info("File: " . $file->getClientOriginalName());
            Log::info("Options: ", $options);

           $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), $options);

            // Sử dụng uploadApi()->upload với file path
            // $result = Cloudinary::uploadApi()->upload($file->getRealPath(), $options);
            Log::info('Cloudinary upload success', [
                'public_id' => $result['public_id'],
                'url' => $result['secure_url'] ?? $result['url'],
            ]);

            return [
                'success'   => true,
                'url'       => $result['secure_url'] ?? $result['url'],
                'public_id' => $result['public_id'],
                'width'     => $result['width'] ?? null,
                'height'    => $result['height'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Cloudinary upload error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function delete(string $publicId): bool
    {
        try {
           $this->cloudinary->uploadApi()->destroy($publicId);
            return true;
        } catch (\Exception $e) {
            Log::error('Cloudinary delete error: ' . $e->getMessage());
            return false;
        }
    }
}
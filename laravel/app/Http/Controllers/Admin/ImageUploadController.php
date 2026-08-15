<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageUploadController extends Controller
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|image|max:5120', // max 5MB
            'color' => 'required|string|max:50',
            'stud_type' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('image');
        $color = $request->input('color');
        $studType = $request->input('stud_type');

        // Tạo public_id duy nhất theo thời gian
        $publicId = 'products/' . date('Y/m/d') . '/' . uniqid() . '_' . time();

        $options = [
            'folder' => 'ecommerce/' . date('Y/m/d'),
            'public_id' => $publicId,
            'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'), // Nếu có preset
        ];

        // Nếu có stud_type, thêm vào public_id để phân biệt
        if ($studType) {
            $options['public_id'] = $publicId . '_' . $studType;
            $options['folder'] = 'ecommerce/' . date('Y/m/d') . '/' . $studType;
        }

        $result = $this->cloudinaryService->upload($file, $options);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Upload failed',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'image_url' => $result['url'],
                'public_id' => $result['public_id'],
                'color' => $color,
                'stud_type' => $studType,
                'width' => $result['width'],
                'height' => $result['height'],
            ],
        ]);
    }
}
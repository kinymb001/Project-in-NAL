<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends BaseController
{
    public function store(Request  $request){

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadedImageId = $this->createUploadRecord($request->file('image'));
        $uploadedVideoId = $this->createUploadRecord($request->file('video'));
        $uploadedCoverImageId = $this->createUploadRecord($request->file('cover_image'));

        $uploadedIds = [
            'image_id' => $uploadedImageId,
            'video_id' => $uploadedVideoId,
            'cover_image_id' => $uploadedCoverImageId,
        ];

        return $this->handleResponseSuccess($uploadedIds, 'image create successfully');
    }

    private function createUploadRecord($files){
        if ($files){
            foreach ($files as $file){
                $path = $file->storeAs('public/upload/' . date('Y/m/d'), Str::random(10));
                $upload = new Upload();
                $upload->url = asset(Storage::url($path));
                $upload->thumbnail = $path;
                $upload->user_id = Auth::id();
                $upload->save();

                return $upload->id;
            }
        }
        return null;
    }
}

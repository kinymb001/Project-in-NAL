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

        $uploadedIds = [];

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('images')){
            foreach ($request->file('images') as $image){
                $resizedImages = resizeImage($image);
                $path = $image->storeAs('public/upload/' . date('Y/m/d'), Str::random(10));
                $upload = new Upload();
                $upload->url = asset(Storage::url($path));
                $upload->thumbnail = $path;
                $upload->user_id = Auth::id();
                $upload->save();

                $uploadedIds[] = [
                    'original' => $upload,
                    'resize' => $resizedImages,
                ];
            }
        }

        return $this->handleResponseSuccess($uploadedIds, 'image create successfully');
    }
}

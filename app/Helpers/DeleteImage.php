<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Upload;

function deleteImage($upload_ids){
    $user_id = Auth::id();
    foreach ($upload_ids as $upload_id){
        $upload = Upload::find($upload_id);
        if($upload && $upload->user_id === $user_id){
            $upload->status = 'active';
            $upload->save();
        }
    }

    $uploads = Upload::where('user_id', $user_id)
        ->where('status', 'pending')->get();

    foreach ($uploads as $upload){
        Storage::delete($upload->thumbnail);
        $upload->delete();
    }
}

<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

function resizeImage($image){
    //lay kich thuoc goc.

    $old = Image::make($image);
    $old_width = $old->getWidth();
    $old_height =  $old->getHeight();
    //mang kich thuoc:
    $resize_pattern = [
        '720x2000', '1280x2000', '480x2000', '330x2000', '200x2000', '100x2000', '300x300',
    ];

    $resizeLengths = [];

    foreach ($resize_pattern as $size) {
        list($width, $height) = explode('x', $size);
        $length = abs($old_width - $width) + abs($old_height - $height);
        $resizeLengths[$size] = $length;
    }

    asort($resizeLengths);
    $closestSize = key($resizeLengths);

    $path = $image->storeAs('public/resize/' . date('Y/m/d'), Str::random(5).$closestSize);
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    list($width, $height) = explode('x', $closestSize);
    $old->resize($width, $height)->save($path . $closestSize);

    $resize_url = asset(Storage::url($path));

    return $resize_url;

}

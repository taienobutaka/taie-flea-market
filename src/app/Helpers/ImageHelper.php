<?php

namespace App\Helpers;

class ImageHelper
{
    /**
     * 画像パスを適切にエンコードしてURLを生成
     *
     * @param string $imagePath
     * @return string
     */
    public static function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return asset('img/no-image.png');
        }

        // パスを分解して各部分をエンコード
        $pathParts = explode('/', $imagePath);
        $encodedParts = array_map(function($part) {
            return urlencode($part);
        }, $pathParts);

        $encodedPath = implode('/', $encodedParts);
        
        return asset('storage/' . $encodedPath);
    }

    /**
     * 画像が存在するかチェック
     *
     * @param string $imagePath
     * @return bool
     */
    public static function imageExists($imagePath)
    {
        if (empty($imagePath)) {
            return false;
        }

        return \Storage::exists('public/' . $imagePath);
    }
} 
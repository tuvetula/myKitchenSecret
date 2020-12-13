<?php


namespace App\Http\Business;


use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PictureBusiness
{
    const PUBLIC_STORAGE_FOLDER = "public/";
    const OLD_PICTURE_STORAGE_FOLDER =  "oldPictures/";
    const PICTURES_FOLDER = "pictures/";
    /**
     * Store a new image
     * @param $picture
     * @param $folder
     * @return mixed
     */
    public static function storeImage($picture,$folder = '')
    {
        return $picture->store(self::PICTURES_FOLDER.$folder , 'public');
    }

    /**
     * Move a picture from public folder to oldPictures folder
     * @param string $path
     */
    public static function movePictureFromPublicFolder(string $path){
        if($path){
            $oldPath = self::PUBLIC_STORAGE_FOLDER.$path;
            $newPath = self::OLD_PICTURE_STORAGE_FOLDER.$path;
            if(Storage::exists($oldPath)){
                Storage::move($oldPath , $newPath);
            }
        }
    }

    public static function deletePicture(string $path){
        if($path){
            if(Storage::exists(self::PUBLIC_STORAGE_FOLDER.$path)){
                Storage::delete(self::PUBLIC_STORAGE_FOLDER.$path);
            }
        }
    }
    /**
     * Get public Url of a file
     * @param string $path
     * @return string
     */
    public static function getPublicUrlFile(string $path){
        return config('app.url').Storage::url($path);
    }
}

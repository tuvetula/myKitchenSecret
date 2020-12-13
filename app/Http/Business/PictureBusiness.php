<?php


namespace App\Http\Business;


use Illuminate\Support\Facades\Storage;

class PictureBusiness
{
    const PICTURES_FOLDER = "pictures";
    /**
     * Store a new image
     * @param $picture
     * @param $folder
     * @return mixed
     */
    public static function storeImage($picture,$folder)
    {
        return $picture->store(self::PICTURES_FOLDER.'/'.$folder , 'public');
    }

    /**
     * Move the old picture when update or destroy in the old folder
     * @param string $path
     */
    public static function moveOldPictureFile(string $path){
        if($path){
            $oldPath = 'public/'.$path;
            $newPath = 'oldPictures/'.$path;
            if(Storage::exists($oldPath)){
                Storage::move($oldPath , $newPath);
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

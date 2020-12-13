<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    const PICTURE_PATH = 'pictures/recipes';
    const PICTURE_DELETE_PATH = 'old/pictures/path';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'preparation_time',
        'baking_time',
        'author_comment',
        'picture',
    ];
}

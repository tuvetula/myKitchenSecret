<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    const PICTURE_PATH = 'pictures/recipes';
    const PICTURE_DELETE_PATH = 'old/pictures/path';
    const VALIDATION_RULES = [
        'title' => 'required|string',
        'content' => 'required|string',
        'preparation_time' => 'integer',
        'baking_time' => 'integer',
        'author_comment' => 'string',
        'picture' => 'mimes:jpeg,png|max:2048',
        'share_status_id' => 'required|integer'
    ];

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
        'share_status_id',
        'user_id'
    ];
}

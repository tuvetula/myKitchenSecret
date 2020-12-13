<?php


namespace App\Http\Business;


use Symfony\Component\HttpFoundation\Request;

class RecipeBusiness
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function validateInputData(Request $request)
    {
        return $request->validate([
            'title' => 'string',
            'content' => 'string',
            'preparation_time' => 'integer',
            'baking_time' => 'integer',
            'author_comment' => 'string',
            'picture' => 'mimes:jpeg,png|max:2048',
            'share_status' => 'integer'
        ]);
    }
}

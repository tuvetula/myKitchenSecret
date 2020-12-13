<?php

namespace App\Http\Controllers\Api;

use App\Http\Business\PictureBusiness;
use App\Http\Business\RecipeBusiness;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RecipeController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $recipes = Recipe::all();
        return $this->sendResponse($recipes,'Recipes retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $recipe = new RecipeResource(Recipe::findOrFail($id));
            return $this->sendResponse($recipe,'RecipeResource retrieved successfully');
        }catch (ModelNotFoundException $exception)
        {
            Log::channel('recipe')->error($exception->getMessage(),['recipe_id'=>$id]);
            return $this->sendError('The resource does not exist.',[],Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @param RecipeBusiness $recipeBusiness
     * @return JsonResponse
     */
    public function update(Request $request, $id, RecipeBusiness $recipeBusiness): JsonResponse
    {
        $validatedData = $recipeBusiness->validateInputData($request);

        try {
            $recipe = Recipe::findOrFail($id);

            // Check if there's a picture
            if($request->hasFile('picture') && $request->file('picture')->isValid()){
                $validatedData['picture'] = $request->picture->store(Recipe::PICTURE_PATH,'public');
                PictureBusiness::moveOldPictureFile($recipe->picture);
            }
            $recipe->update($validatedData);
            return $this->sendResponse(new RecipeResource($recipe),'Recipe updated successfully');
        }catch (ModelNotFoundException $exception)
        {
            Log::channel('recipe')->error($exception->getMessage(),['recipe_id'=>$id]);
            return $this->sendError('The resource does not exist.',[],Response::HTTP_NOT_FOUND);
        } catch (\Exception $exception)
        {
            Log::channel('recipe')->error($exception->getMessage(),['recipe_id'=>$id]);
            return $this->sendError('Update failed',[],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try{
            $recipe = Recipe::findOrFail($id);
            $recipe->delete();
            return $this->sendResponse([],'The resource has been deleted successfully',Response::HTTP_NO_CONTENT);
        }catch(ModelNotFoundException $exception)
        {
            Log::channel('recipe')->error($exception->getMessage(),['recipe_id'=>$id]);
            return $this->sendError('The resource does not exist.',[],Response::HTTP_NOT_FOUND);
        }
    }
}

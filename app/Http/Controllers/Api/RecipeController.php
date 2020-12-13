<?php

namespace App\Http\Controllers\Api;

use App\Http\Business\PictureBusiness;
use App\Http\Business\RecipeBusiness;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
     * @param RecipeBusiness $recipeBusiness
     * @return JsonResponse
     */
    public function store(Request $request,RecipeBusiness $recipeBusiness): JsonResponse
    {
        try {
            $validatedData = $request->validate(Recipe::VALIDATION_RULES);
            $validatedData['user_id'] = Auth::id();
            $recipe = Recipe::create($validatedData);
            Log::channel('recipe')->info('New recipe has been created',[
                'id' => $recipe->id,
                'user_id' => Auth::id()
            ]);
            return $this->sendResponse($recipe,'The recipe has been successfully created',Response::HTTP_CREATED);
        }catch (ValidationException $exception){
            Log::channel('recipe')->error($exception->getMessage());
            return $this->sendError($exception->getMessage(),$exception->errors(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        catch(Exception $exception)
        {
            Log::channel('recipe')->error($exception->getMessage());
            return $this->sendError('Error during creating resource.',[],Response::HTTP_INTERNAL_SERVER_ERROR);
        }

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
        try {
            $validatedData = $request->validate(Recipe::VALIDATION_RULES);
            $recipe = Recipe::findOrFail($id);

            // Check if there's a picture
            if($request->hasFile('picture') && $request->file('picture')->isValid()){
                $validatedData['picture'] = $request->picture->store(Recipe::PICTURE_PATH,'public');
                PictureBusiness::moveOldPictureFile($recipe->picture);
            }
            $recipe->update($validatedData);
            return $this->sendResponse(new RecipeResource($recipe),'Recipe updated successfully');
        }catch(ValidationException $exception)
        {
            Log::channel('recipe')->warning('['.get_class($exception).'] - '.$exception->getMessage(),[
                'user_id' => Auth::id(),
                'recipe_id' => $id,
                'request' => $request->all()
            ]);
            return $this->sendError('The given data was invalid.',$exception->errors(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        catch (ModelNotFoundException $exception)
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

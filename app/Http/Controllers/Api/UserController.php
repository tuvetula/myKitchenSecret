<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try{
            $users = User::all();
            return $this->sendResponse($users,'Users retrieved successfully');
        }catch(ModelNotFoundException $exception)
        {
            Log::channel('user')->error($request->method().' '.$exception->getMessage(),[
                'id' => Auth::id()
            ]);
            return $this->sendError('The resource does not exist.',[]);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try{
            $validatedData = $request->validate([
                'name' => 'required|string|min:2',
                'first_name' => 'required|string|min:2',
                'email' => 'required|unique:App\Models\User,email|email',
                'password' => 'required',
                'confirm_password' => 'required|same:password'
            ]);
            $validatedData['password'] = bcrypt($validatedData['password']);
            $validatedData['is_admin'] = false;
            $user = new UserResource(User::create($validatedData));
            Log::channel(User::LOG_CHANNEL)->info('New user has been registered',[
                'user_id' =>Auth::id(),
                'new_user_registered_id' => $user->id
            ]);
            return $this->sendResponse($user,'User register successfully.');
        }
        catch(ValidationException $exception)
        {
            Log::channel(User::LOG_CHANNEL)->warning($request->method().' '.$exception->getMessage(),[
                'user_id' => Auth::id(),
                'request' => $request->all(),
                'errors' => $exception->errors()
            ]);
            return $this->sendError($exception->getMessage(),$exception->errors(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        catch(QueryException $exception)
        {
            Log::channel(User::LOG_CHANNEL)->info($request->method().' '.$exception->getMessage(),[
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);
            return $this->sendError('User with this email address already exist',Response::HTTP_CONFLICT);
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
        try{
            $user = new UserResource(User::findOrFail($id));
            return $this->sendResponse($user,'User retrieved successfully');
        }
        catch(ModelNotFoundException $exception)
        {
            Log::channel(User::LOG_CHANNEL)->warning('[ModelNotFoundException] - ',[
                'user_id' => Auth::id(),
                'user_search_id' => $id
            ]);
            return $this->sendError('The resource does not exist',[],Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try{
            $user = User::findOrFail($id);
            $user->delete();

            return $this->sendResponse([],'User deleted successfully',Response::HTTP_NO_CONTENT);
        }
        catch(ModelNotFoundException $exception)
        {
            Log::channel(User::LOG_CHANNEL)->warning('[ModelNotFoundException] - ',[
                'user_id' => Auth::id(),
                'user_search_id' => $id
            ]);
            return $this->sendError('The resource does not exist',[],Response::HTTP_NOT_FOUND);
        }
    }
}

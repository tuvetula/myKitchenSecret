<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'first_name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string|min:8',
                'confirm_password' => 'required|string|min:8|same:password',
            ]);
            $validatedData['password'] = bcrypt($validatedData['password']);
            $validatedData['is_admin'] = false;
            $user = new UserResource(User::create($validatedData));

            Log::channel(config('logging.channels.authentication.name'))->info('New user has been registered',[
                'id'=>$user->id,
                'name'=>$user->name,
                'first_name'=>$user->first_name
            ]);
            return $this->sendResponse($user, 'User register successfully. You have to login to access resources');
        }
        catch(ValidationException $exception)
        {
            Log::channel(config('logging.channels.authentication.name'))->info($exception->getMessage(),[
                'request' => $request->all(),
                'errors' => $exception->errors()
            ]);
            return $this->sendError($exception->getMessage(),$exception->errors(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        catch(QueryException $exception)
        {
            Log::channel(config('logging.channels.authentication.name'))->info($exception->getMessage(),[
                'request' => $request->all()
            ]);
            return $this->sendError('User with this email address already exist',$request->all(),Response::HTTP_CONFLICT);
        }
    }

    /**
     * Login api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try{

            $login = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string'
            ]);

            if(!Auth::attempt($login)){
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised'],Response::HTTP_FORBIDDEN);
            }

            $user = Auth::user();
            $response['token'] =  $user->createToken(config('app.name'))->accessToken;

            return $this->sendResponse($response, 'User login successfully.');
        }
        catch(ValidationException $exception)
        {
            Log::channel(config('logging.channels.authentication.name'))->info($exception->getMessage(),[
                'request' => $request->all(),
                'errors' => $exception->errors()
            ]);
            return $this->sendError($exception->getMessage(),$exception->errors(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Logout Api
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try{
            $user = Auth::user();
            $user->token()->revoke();
            return $this->sendResponse([], 'User logout successfully');
        } catch (Exception $exception)
        {
            Log::channel(config('logging.channels.authentication.name'))->error($exception->getMessage(),['user_id' => Auth::id()]);
            return $this->sendError('Error with logout. Retry later');
        }

    }
}

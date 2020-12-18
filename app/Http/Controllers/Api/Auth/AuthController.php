<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Business\AuthenticationBusiness;
use App\Http\Controllers\Api\BaseController;
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
    protected $authenticationBusiness;

    public function __construct(AuthenticationBusiness $authenticationBusiness)
    {
        $this->authenticationBusiness = $authenticationBusiness;
    }

    /**
     * Register api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'first_name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|string|min:8',
                'confirm_password' => 'required|string|min:8|same:password',
            ]);
            $password = $validatedData['password'];
            $validatedData['password'] = bcrypt($validatedData['password']);
            $validatedData['is_admin'] = false;
            $user = User::create($validatedData);
            Log::channel(config('authentication'))->info('New user has been registered',[
                'id'=>$user->id,
                'name'=>$user->name,
                'first_name'=>$user->first_name
            ]);
            $response = $this->authenticationBusiness->grantPasswordToken($user->email,$request['password']);

            return $this->sendResponse($response, 'User register successfully. You have to login to access resources');
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
                return $this->sendError('Unauthorised.', ['error'=>'Unauthorised'],Response::HTTP_UNAUTHORIZED);
            }
            $response = $this->authenticationBusiness->grantPasswordToken($request['email'], $request['password']);

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

    public function refreshToken()
    {
        $response = $this->authenticationBusiness->refreshAccessToken();
        return $this->sendResponse($response,'Token has been refreshed');
    }

    /**
     * Logout Api
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try{
            $token = request()->user()->token();
            $token->delete();

            // remove the httponly cookie
            cookie()->queue(cookie()->forget('refresh_token'));
            return $this->sendResponse([], 'You have been successfully logged out');
        } catch (Exception $exception)
        {
            Log::channel(config('logging.channels.authentication.name'))->error($exception->getMessage(),['user_id' => Auth::id()]);
            return $this->sendError('Error with logout. Retry later');
        }

    }
}

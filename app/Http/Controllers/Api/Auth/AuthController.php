<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\AuthException;
use App\Http\Business\AuthenticationBusiness;
use App\Http\Business\ResponseJsonBusiness;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\RegisterUserNotification;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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
     * @param RegisterRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create($request->validated());
            Notification::send($user,new RegisterUserNotification());
            return ResponseJsonBusiness::sendSuccess(new UserResource($user), 'User register successfully. You have to login to access resources');
        }
        catch(QueryException $exception)
        {
            throw new AuthException('Registration failed! User with this email address already exist.',
                Response::HTTP_CONFLICT
            );
        }
    }

    /**
     * Login api
     *
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if(!Auth::attempt($request->validated())){
            throw new AuthException('login failed! Check email or password.',Response::HTTP_FORBIDDEN);
        }
        $response = $this->authenticationBusiness->grantPasswordToken($request['email'], $request['password']);
        return ResponseJsonBusiness::sendSuccess($response, 'User login successfully.');
    }

    /**
     * refresh token
     * @return JsonResponse
     * @throws Exception
     */
    public function refreshToken(): JsonResponse
    {
        $response = $this->authenticationBusiness->refreshAccessToken();
        return ResponseJsonBusiness::sendSuccess($response,'Token has been refreshed');
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

            return ResponseJsonBusiness::sendSuccess([],'You have been successfully logged out',);
        }
        catch (Exception $exception)
        {
            Log::channel(config('logging.channels.authentication.name'))->error($exception->getMessage(),['user_id' => Auth::id()]);
            return ResponseJsonBusiness::sendError( 'Error with logout. Retry later');
        }
    }
}

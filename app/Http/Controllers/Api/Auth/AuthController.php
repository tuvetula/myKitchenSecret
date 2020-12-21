<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\AuthException;
use App\Http\Business\AuthenticationBusiness;
use App\Http\Business\ResponseJsonBusiness;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\Auth\ForgetPasswordNotification;
use App\Notifications\Auth\RegisterUserNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * Confirm email address
     * @param VerifyEmailRequest $request
     * @param $id
     * @return JsonResponse
     * @throws AuthException
     */
    public function verifyEmail(VerifyEmailRequest $request,$id): JsonResponse
    {
        try{
            $validatedData = $request->validated();
            $user = User::findOrFail($id);
            if($user->remember_token == $validatedData['token'])
            {
                $user->setEmailVerifiedAt(Carbon::now());
                $user->setRememberToken(null);
                $user->save();
                // TODO redirect to front-end
                return ResponseJsonBusiness::sendSuccess(new UserResource($user),'Email verified with success');
            } else {
                throw new AuthException('Token verify email is invalid!',Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }catch(ModelNotFoundException $exception)
        {
            return ResponseJsonBusiness::sendError('There is no account with this email address' ,$request->only('email'),Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Send mail to user for reset password
     * @param ForgetPasswordRequest $request
     * @return JsonResponse
     */
    public function forgetPassword(ForgetPasswordRequest $request): JsonResponse
    {
        try{
            $validatedData = $request->validated();
            $user = User::where('email', $validatedData['email'])->firstOrFail();
            $user->setRememberToken(uniqid());
            $user->save();
            Notification::locale('fr')->send($user,new ForgetPasswordNotification());
            return ResponseJsonBusiness::sendSuccess([],trans_choice('auth.checkMailBoxForgetPassword',0,[],'fr'));
        }catch(ModelNotFoundException $exception)
        {
            return ResponseJsonBusiness::sendError('No account with this email address' ,$request->only('email'),Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Reset user password
     * @param ResetPasswordRequest $request
     * @param $id
     * @return JsonResponse
     * @throws AuthException
     */
    public function resetPassword(ResetPasswordRequest $request,$id): JsonResponse
    {
        try{

            $validatedData = $request->validated();
            $user = User::findOrFail($id);
            if($user->getRememberToken() && $validatedData['token'] == $user->getRememberToken())
            {
                $user->setPassword($request->input('password'));
                if(!$user->getEmailVerifiedAt())
                {
                    $user->setEmailVerifiedAt(Carbon::now());
                }
                $user->setRememberToken(null);
                $user->save();
                return ResponseJsonBusiness::sendSuccess([],'Your password has been successfully reset.');
            } else {
                throw new AuthException('Token modify password is invalid!',Response::HTTP_UNAUTHORIZED,[
                    'request_user_id' => $id,
                    'request_token' => $request->input('token'),
                    'user_token' => $user->getRememberToken()
                ]);
            }
        }catch(ModelNotFoundException $exception)
        {
            return ResponseJsonBusiness::sendError('There is no account with this email address' ,$request->only('email'),Response::HTTP_NOT_FOUND);
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

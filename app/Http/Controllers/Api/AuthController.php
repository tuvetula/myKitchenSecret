<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'first_name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['is_admin'] = false;
        try {
            $user = User::create($input);
        }catch(QueryException $exception)
        {
            return $this->sendError('User with this email address already exist',[],409);
        }
        $success['token'] =  $user->createToken(config('app.name'))->accessToken;
        $success['name'] =  $user->name;
        Log::channel('authentication')->info('New user has been registered',['id'=>$user->id,'name'=>$user-name,'first_name'=>$user->first_name]);
        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $login = $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        if(!Auth::attempt($login)){
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

        $user = Auth::user();
        $success['token'] =  $user->createToken(config('app.name'))-> accessToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User login successfully.');
    }

    public function logout()
    {
        try{
            $user = Auth::user();
            $user->token()->revoke();
            return $this->sendResponse([], 'User logout successfully');
        } catch (\Exception $exception)
        {
            Log::channel('authentication')->error($exception->getMessage(),['user_id' => Auth::id()]);
            return $this->sendError('Error with logout. Retry later');
        }

    }
}

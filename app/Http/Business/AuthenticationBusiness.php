<?php


namespace App\Http\Business;

use App\Exceptions\AuthException;
use Exception;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AuthenticationBusiness
{
    /**
     * Get grant access token
     * @param string $email
     * @param string $password
     * @return mixed
     * @throws Exception
     */
    public function grantPasswordToken(string $email, string $password){
        $params = [
            'grant_type' => 'password',
            'username' => $email,
            'password' => $password
        ];
        return $this->makePostRequest($params);
    }

    /**
     * Refresh access token
     * @return mixed
     * @throws Exception
     */
    public function refreshAccessToken()
    {
        $refreshToken = request()->cookie('refresh_token');
        abort_unless($refreshToken,403,'Your refresh token is expired');
        $params = [
          'grant_type' => 'refresh_token',
          'refresh_token' => $refreshToken
        ];
        return $this->makePostRequest($params);
    }

    /**
     * make Post Request
     * @param array $params
     * @return mixed
     * @throws AuthException
     */
    protected function makePostRequest(array $params)
    {
        $params = array_merge([
            'client_id' => config('services.passport.password_client_id'),
            'client_secret' => config('services.passport.password_client_secret'),
            'scope' => '*'
        ],$params);
        try{
            $proxy = \Request::create('oauth/token','post',$params);
            $response = json_decode(app()->handle($proxy)->getContent());
            $this->setHttpOnlyCookie($response->refresh_token);
            return $response;
        }catch(Exception $exception)
        {
            throw new AuthException('[MakePostRequest] -'.$exception->getMessage(),$exception->getCode());
        }
    }

    /**
     * Set http Only Cookie
     * @param string $refreshToken
     */
    protected function setHttpOnlyCookie(string $refreshToken)
    {
        cookie()->queue(
            'refresh_token',
            $refreshToken,
            14400,
            null,
            null,
            false,
            true
        );
    }
}

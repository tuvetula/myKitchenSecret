<?php


namespace App\Http\Business;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client as OClient;


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
     * @throws Exception
     */
    protected function makePostRequest(array $params)
    {
        $params = array_merge([
            'client_id' => config('services.passport.password_client_id'),
            'client_secret' => config('services.passport.password_client_secret'),
            'scope' => '*'
        ],$params);
        $proxy = \Request::create('oauth/token','post',$params);
        $response = json_decode(app()->handle($proxy)->getContent());
        $this->setHttpOnlyCookie($response->refresh_token);
        return $response;
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
    public function getTokenAndRefreshToken($email, $password) {
        try {

            $oClient = OClient::where('password_client', 1)->firstOrFail();
            $response = Http::asForm()->post( 'http://localhost:8000/oauth/token', [
                    'grant_type' => 'client_credentials',
                    'client_id' => $oClient->id,
                    'client_secret' => $oClient->secret,
                    'username' => $email,
                    'password' => $password,
                    'scope' => '*',
            ]);
            return $response;
        }
        catch(ModelNotFoundException $exception)
        {
            return 'coucou';
        }
        catch(Exception $exception){
            return 'merde';
        }
    }
}

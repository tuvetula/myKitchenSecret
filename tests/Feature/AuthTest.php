<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class AuthTest extends TestCase
{

    /**
     * @test
     * Test registration
     */
    public function testRegister()
    {
        //User's data
        $data = [
            'email' => 'test@gmail.com',
            'name' => 'testName',
            'first_name' => 'firstNameTest',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ];
        //Send post request
        $response = $this->json('POST', route('api.register'), $data);
        //Delete data
        User::where('email', $data['email'])->forceDelete();
        //Assert it was successful
        $response->assertStatus(200);
        //Assert we received data
        $this->assertArrayHasKey('data', $response->json());
        //Assert we have access_token
        $this->assertArrayHasKey('access_token', $response->json()['data']);
        //Assert we have refresh_token
        $this->assertArrayHasKey('refresh_token', $response->json()['data']);
    }

    /**
     * @test
     * Test login
     */
    public function testLogin()
    {
        //Create user
        $user = User::factory()->create();
        //attempt login
        $responseLogin = $this->json('POST', route('api.login'), [
            'email' => $user['email'],
            'password' => 'secretFaker',
        ]);
        //Assert it was successful
        $responseLogin->assertStatus(200);
        //Assert we received data
        $this->assertArrayHasKey('data', $responseLogin->json());
        //Assert we have access_token
        $this->assertArrayHasKey('access_token', $responseLogin->json()['data']);
        //Assert we have refresh_token
        $this->assertArrayHasKey('refresh_token', $responseLogin->json()['data']);
        //Assert response have a the refresh_token cookie
        $responseLogin->assertCookie('refresh_token');
        //Delete the user
        User::where('email', $user['email'])->forceDelete();
    }

    /**
     * @test
     * Test refresh_token
     */
    public function testRefreshToken()
    {
        //Create user
        $user = User::factory()->create();
        //attempt login
        $responseLogin = $this->json('POST', route('api.login'), [
            'email' => $user['email'],
            'password' => 'secretFaker',
        ]);
        //Assert it was successful and a token was received
        $responseLogin->assertStatus(200);
        //Assert we received data
        $this->assertArrayHasKey('data', $responseLogin->json());
        //Assert we have access_token
        $this->assertArrayHasKey('access_token', $responseLogin->json()['data']);
        //Assert we have refresh_token
        $this->assertArrayHasKey('refresh_token', $responseLogin->json()['data']);
        //Assert response have a the refresh_token cookie
        $responseLogin->assertCookie('refresh_token');
        $cookie = $responseLogin->json()['data']['refresh_token'];

        // Attempt refresh token
        $this->disableCookieEncryption();
        $responseRefreshToken = $this->withCookie('refresh_token', $cookie)
            ->post(route('api.refresh_token'));
        //Assert it was successful and a token was received
        $responseRefreshToken->assertStatus(200);
        //Assert we received data
        $this->assertArrayHasKey('data', $responseRefreshToken->json());
        //Assert we have access_token
        $this->assertArrayHasKey('access_token', $responseRefreshToken->json()['data']);
        //Assert we have refresh_token
        $this->assertArrayHasKey('refresh_token', $responseRefreshToken->json()['data']);
        //Delete the user
        User::where('email', $user['email'])->forceDelete();
    }

    /**
     * @test
     * Test logout
     */
    public function testLogout()
    {
        //Create user
        $user = User::factory()->create();
        //attempt login
        $responseLogin = $this->json('POST', route('api.login'), [
            'email' => $user['email'],
            'password' => 'secretFaker',
        ]);
        //Assert it was successful
        $responseLogin->assertStatus(200);
        //Assert we received data
        $this->assertArrayHasKey('data', $responseLogin->json());
        //Assert we have access_token
        $this->assertArrayHasKey('access_token', $responseLogin->json()['data']);
        $cookie = $responseLogin->json()['data']['refresh_token'];
        $this->disableCookieEncryption();
        $responseLogout = $this->withCookie('refresh_token', $cookie)->withHeader('Authorization', 'Bearer ' . $responseLogin->json()['data']['access_token'])
            ->post(route('api.logout'));
        //Assert it was successful
        $responseLogout->assertStatus(200);
        //Delete the user
        User::where('email', $user['email'])->forceDelete();
    }
}

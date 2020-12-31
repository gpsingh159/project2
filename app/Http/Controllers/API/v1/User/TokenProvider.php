<?php

namespace App\Http\Controllers\API\v1\User;

use App\Models\User;
use Laravel\Passport\Http\Controllers\ConvertsPsrResponses;
use League\OAuth2\Server\AuthorizationServer;
use Zend\Diactoros\Response as Psr7Response;
use Zend\Diactoros\ServerRequest;
use Laravel\Passport\Passport;
use Illuminate\Support\Str;

class TokenProvider
{
    use ConvertsPsrResponses;

    /**
     * @var AuthorizationServer
     */
    private $authServer;
    private $encrypter;

    public function __construct()
    {
        $this->authServer = resolve(AuthorizationServer::class);
        $this->encrypter = app('Illuminate\Contracts\Encryption\Encrypter');
    }

    public function getNewToken(int $userId, string $email, string $password,$scope='')
    {
        $client = $this->getClient($userId);
        $data = [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->mysecret,
            'username' => $email,
            'password' => $password,
            'scope' => $scope,
        ];

        try {
            // request for get access token
            $request = new ServerRequest($data);
            $request = $request->withParsedBody($data);
            $response = $this->convertResponse($this->authServer->respondToAccessTokenRequest($request, new Psr7Response));
            $getToken = json_decode($response->content());
            $encryptUser = $this->encrypter->encrypt($userId);
            
            $Token['token_type'] = $getToken->token_type;
            $Token['expires_in'] = $getToken->expires_in;
            $Token['access_token'] = $getToken->access_token;
            $Token['refresh_token'] = $getToken->refresh_token . '.' . $encryptUser;
            return $Token;
        } catch (\Exception $e) {
            $errors = $e->getMessage();
            $response = [
                'success' => false,
                'errors' => ['message' => $errors],
            ];
            return $response;
        }
    }

    public function getRefreshToken($refreshToken, $clientId, $clientSecret,$scope='')
    {
        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
        ];
        try {
            $request = new ServerRequest($data);
            $request = $request->withParsedBody($data);
            $response = $this->convertResponse($this->authServer->respondToAccessTokenRequest($request, new Psr7Response));
            return json_decode($response->content());
        } catch (\Exception $e) {
            $errors = $e->getMessage();
            $response = [
                'success' => false,
                'errors' => ['message' => $errors],
            ];
            return $response;
        }
    }

    public function getClient($userId)
    {
        $client = User::find($userId)->clients->makeVisible('secret')->first();
        if (!$client) {
            throw new \ErrorException('Client not found');
        }
        $secret = $client->secret;
        $client->mysecret = $secret;
        return $client;
    }

    public function createClient($userId, $clientName)
    {
        $secret = Str::random(40);
        $redirect = env('APP_URL', '/');
        try {
            $client = Passport::client()->forceFill([
                'user_id' => $userId,
                'name' => $clientName,
                'secret' => $secret,
                'redirect' => $redirect,
                'personal_access_client' => 0,
                'password_client' => 1,
                'revoked' => false,
            ]);
            $client->save();
            $client->mysecret = $secret;
            return $client;
        } catch (\Exception $e) {
            // handling exception
            throw new \ErrorException('Error occurred during client creation');
        }
    }

    public function userRefreshToken($refreshToken,$scope='')
    {
        $tokenSegments = explode('.', $refreshToken);
        if (count($tokenSegments) != 2) {
            throw new \ErrorException('The refresh token is invalid');
        }
        try {
            $decryptedUser = $this->encrypter->decrypt($tokenSegments[1]);
            $Client = User::find($decryptedUser)->clients->makeVisible('secret')->first();
            return $this->getRefreshToken($tokenSegments[0], $Client->id, $Client->secret,$scope);
        } catch (\Exception $e) {
            // handling exception
            throw new \ErrorException('The refresh token is invalid');;
        }
    }
}

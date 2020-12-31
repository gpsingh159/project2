<?php

namespace App\Http\Middleware;

use App\Traits\ResponseFromatTrait;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');

            //return $this->errors('UNAUTHORIZED', Response::HTTP_UNAUTHORIZED);;
        }
    }
    public function errors($errors = null, $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $response = [
            'success' => false,
            'errors' => ['message' => $errors],
        ];
        return response()->json($response, $httpCode);
    }
}

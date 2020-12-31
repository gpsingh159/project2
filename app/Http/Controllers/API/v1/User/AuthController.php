<?php

namespace App\Http\Controllers\API\v1\User;

use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

use Exception;
use Hash;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use App\Custom\Files\AwsS3;
use App\Http\Controllers\API\v1\User\TokenProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Validator;

class AuthController extends CommonController
{

    /**
     * AuthenticatesUsers  traits provde auth() funtionlity
     */
    use AuthenticatesUsers;

    /**
     * Sigup function
     *
     * @param Request $request
     * @return void
     */
    public function signup(Request $request)
    {
        // begin transaction 
        \DB::beginTransaction();

        try {
            $input = $request->all();
            $rules = [
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|confirmed|min:8',
                'role' => 'required|numeric|min:2|max:4',
            ];
            $validationMessage = [
                'role.min' => "Role id is invalid" ,
                'role.max' => "Role id is invalid"
            ];
            // validating input
            $validator = Validator::make($input, $rules, $validationMessage);

            // check valitions
            if ($validator->fails()) {
                return $this->validationError($validator->errors());
            }

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => $request->role,

            ];
           

            $user = new User($data);
            $user->save();
            // create client
            $client = new TokenProvider();
            $client->createClient($user->id, $request->name . ' Client');
            // end of transaction 
            \DB::commit();
            return $this->success("User created successfully");
        } catch (\Exception $e) {
            // handling exception
            \DB::rollback();
            return $this->errors($e->getMessage());
        }
    }

    public function login(Request $request)
    {

        $input = $request->all();
        $rules = [
            'email' => 'required',
            'password' => 'required',
        ];
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        //check how many times login attempt
        if ($this->hasTooManyLoginAttempts($request)) {

            $this->fireLockoutEvent($request);
            return $this->errors('Too many login attempts', Response::HTTP_TOO_MANY_REQUESTS);
        }

        $credentials = request(['email', 'password']);

        try {
            if (Auth::attempt($credentials)) {
                // clear login Attempt
                $this->clearLoginAttempts($request);
                // get access token
                $token = new TokenProvider();
                $getToken = $token->getNewToken(auth::user()->id, request('email'), request('password'));

                return $this->success("Success", Response::HTTP_OK, $getToken);
            } else {
                $this->incrementLoginAttempts($request);
                return $this->errors('Wrong Email or password', Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {

            return $this->errors($e->getMessage());
        }
    }

    protected function hasTooManyLoginAttempts(Request $request)
    {

        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($request),
            config('contants.maxLoginAttempts'),
            config('contants.lockoutMinute')
        );
    }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return $this->success("Success", Response::HTTP_OK, auth()->guard('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->guard('api')->user()->token()->revoke();
        return $this->success("Successfully logged out", Response::HTTP_OK);
    }

}
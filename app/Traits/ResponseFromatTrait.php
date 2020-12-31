<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait ResponseFromatTrait
{
    /**
     * Success function
     * This is common formate for api success repaonse
     * @param [type] $message
     * @param [type] $httpCode
     * @param [type] $result
     * @return void
     */
    public function success($message = null, $httpCode = Response::HTTP_OK, $result = null)
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];
        return response()->json($response, $httpCode);
    }

    /**
     * ValidationError function
     *  This is common function for reponse for validation error
     * @param [type] $validationErrors
     * @return void
     */
    public function validationError($validationErrors)
    {
        $response = [
            'success' => false,
            'errors' => array_map('current', $validationErrors->toArray()),
        ];
        return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
    /**
     * Errors function
     * This is common function for response all errors  occurs 
     * @param [type] $errors
     * @param [type] $httpCode
     * @return void
     */
    public function errors($errors = null, $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $response = [
            'success' => false,
            'errors' => ['message' => $errors],
        ];
        return response()->json($response, $httpCode);
    }
}

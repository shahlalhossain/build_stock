<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Handler extends ExceptionHandler
{
    /**
     * Prepare the response for unauthenticated users.
     *
     * @param  Request  $request
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse
    {
        //        if ($request->expectsJson()) {
        return response()->json(['message' => 'Unauthenticated. Please Provide a Valid API Token.'], 401);
        //        }
    }
}

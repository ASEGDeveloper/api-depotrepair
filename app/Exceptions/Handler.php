<?php

namespace App\Exceptions; 

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


class Handler extends ExceptionHandler
{ 

   public function render($request, Throwable $exception)
    {
    if ($request->is('api/*')) {
        $status = 500;
        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
        }

        return response()->json([
            'message' => $exception->getMessage() ?: 'Server Error',
            'status' => $status
        ], $status);
    }

    return parent::render($request, $exception);
    }

    // Existing properties and methods...

    /**
     * Convert an authentication exception into a response.
     */
   protected function unauthenticated($request, AuthenticationException $exception)
    {
    return response()->json([
        'message' => 'Unauthenticated.'
    ], 401);
    
    }

}

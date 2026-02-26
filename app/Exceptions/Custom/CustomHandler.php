<?php

namespace App\Exceptions\Custom;

use App\Exceptions\Custom\ApplicationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Support\Facades\Log;

class CustomHandler
{ 
    /**
     * Render exceptions for AJAX / fetch requests
     */
    public static function render($request, Throwable $exception)
    {  
        // Controlled user-visible exception
        if ($exception instanceof ApplicationException || $exception instanceof HttpException) {
            return response()->json([
                'status'  => 'error',
                'message' => $exception->getMessage()
            ], ($exception instanceof ApplicationException) ? $exception->getStatusCode() : $exception->getStatusCode());
        } 
        
        // All other exceptions are internal
        Log::error('Unhandled System Exception', [
            'message' => $exception->getMessage(),
            'trace'   => $exception->getTraceAsString()
        ]);

        return response()->json([
            'status'  => 'error',
            'message' => 'Unexpected system error. Please contact administrator.'
        ], 500);
    } 
}

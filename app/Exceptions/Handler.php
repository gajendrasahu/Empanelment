<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
class Handler extends ExceptionHandler
{
    protected $levels = [
        HttpException::class => 'critical',
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            Log::error('Exception occurred', ['exception' => $e]);
        });
    }

    public function render($request, Throwable $exception)
    {
		if ($exception instanceof ValidationException) {
			Log::info('Validation failed', $this->logContext($exception));

			if ($request->expectsJson()) {
				return response()->json([
					'message' => 'Validation failed',
					'errors' => $exception->errors(),
				], 422);
			}
			
			return redirect()
				->back()
				->withErrors($exception->errors())
				->withInput();
		}		


        if ($request->expectsJson())
		{
            if ($exception instanceof MethodNotAllowedHttpException)
			{
				Log::warning('Invalid HTTP method', $this->logContext($exception));
                return response()->json([
                    'message' => 'Invalid request method.'
                ], 405);
            }

            if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
				Log::notice('Model not found', $this->logContext($exception));
                return response()->json([
                    'message' => 'Resource not found.'
                ], 404);
            }

            if ($exception instanceof HttpException) {
				Log::error('HTTP exception', $this->logContext($exception));
                return response()->json([
                    'message' => 'Something went wrong. Please try again later.'
                ], $exception->getStatusCode() ?? 500);
            }
			
			Log::critical('API unknown exception', $this->logContext($exception));
			
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }

        if ($exception instanceof ThrottleRequestsException) {
			Log::warning('Rate limit exceeded', $this->logContext($exception));
            return redirect()->back()
                ->withInput()
                ->with([
                    'fail' => 'You are submitting too quickly. Please wait a few seconds and try again.'
                ]);
        }

        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
			Log::notice('Model not found (web)', $this->logContext($exception));
            return response()->view('errors.404', [], 404);
        }

        if ($exception instanceof HttpException) {
			Log::error('HTTP exception (web)', $this->logContext($exception));
            return response()->view('errors.generic', [
                'message' => 'Something went wrong. Please try again later.'
            ], $exception->getStatusCode() ?? 500);
        }
		
		Log::critical('Unhandled web exception', $this->logContext($exception));
		
        return response()->view('errors.generic', [
            'message' => 'An unexpected error occurred. Please try again later.'
        ], 500);
    }


    protected function logContext(Throwable $exception): array
    {
        return [
            'exception'	=>	get_class($exception),
            'message' 	=> 	$exception->getMessage(),
            'url' 		=> 	request()->fullUrl(),
            'method' 	=> 	request()->method(),
            'user_id' 	=> 	auth()->id(),
            'ip' 		=> 	request()->ip(),
        ];
    }	
}
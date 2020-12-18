<?php

namespace App\Exceptions;

use App\Http\Business\ResponseJsonBusiness;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'confirm_password',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (ValidationException $exception,$request){
            Log::channel(config('logging.channels.authentication.name'))->warning($exception->getMessage(),[
                'request' => $request->all(),
                'errors' => $exception->errors()
            ]);
            return ResponseJsonBusiness::sendError($exception->getMessage(),$exception->errors(),Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (ModelNotFoundException $exception,$request) {
            return ResponseJsonBusiness::sendError('This resource doesn\'t exist' ,$request->all(),Response::HTTP_INTERNAL_SERVER_ERROR);
        });

        $this->renderable(function (Exception $e) {
           return ResponseJsonBusiness::sendError('Internal server error',Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    }


}

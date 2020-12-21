<?php

namespace App\Exceptions;

use App\Http\Business\ResponseJsonBusiness;
use ErrorException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


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
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (ValidationException $exception,$request){
            return ResponseJsonBusiness::sendError($exception->getMessage(),$exception->errors(),Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (NotFoundHttpException $exception){
            return ResponseJsonBusiness::sendError($exception->getMessage(),[],Response::HTTP_NOT_FOUND);
        });

        $this->renderable(function (Exception $exception) {
            return ResponseJsonBusiness::sendError('Internal server error',[],Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    }


}

<?php

namespace App\Exceptions;

use App\Http\Business\ResponseJsonBusiness;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return false
     */
    public function report(): bool
    {
        if($this->getCode() === Response::HTTP_FORBIDDEN)
        {
            Log::channel('authentication')->notice($this->getMessage(),request()->except('password'));

        } else {
            Log::channel('authentication')->info('['.get_class($this).'] - '.$this->getMessage());
        }
        return false;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        return ResponseJsonBusiness::sendError($this->getMessage(),[],$this->getCode());
    }
}

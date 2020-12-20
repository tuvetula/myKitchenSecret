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
    /**
     * AuthException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return false
     */
    public function report(): bool
    {
        switch ($this->getCode()){
            case Response::HTTP_CONFLICT:
            case Response::HTTP_FORBIDDEN:
                Log::channel(config('logging.channels.authentication.name'))
                    ->notice($this->getMessage(),request()->only('email'));
                break;
            case 'default':
                Log::channel(config('logging.channels.authentication.name'))
                    ->warning($this->getMessage().PHP_EOL.$this->getTraceAsString());
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

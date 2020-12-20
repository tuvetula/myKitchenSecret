<?php


namespace App\Logging;


use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Auth;
use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param Logger $logger
     * @return void
     */
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                '[%datetime%] %channel%.%level_name%: %message% %context% %extra%'.PHP_EOL,
                'Y-m-d H:i:s',
                false,
                true
            ));
            $handler->pushProcessor([$this, 'processLogRecord']);
            //$logger->pushHandler($handler);
        }
    }

    public function processLogRecord(array $record): array
    {
        $record['extra'] += [
            'ip' => request()->ip(),
            'user_id' => Auth::id()
        ];

        return $record;
    }
}

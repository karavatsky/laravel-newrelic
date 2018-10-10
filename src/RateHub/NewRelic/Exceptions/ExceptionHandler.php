<?php

namespace RateHub\NewRelic\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler as IExceptionHandler;
use RateHub\NewRelic\Adapters\NewRelicAgentAdapter;
use RateHub\NewRelic\Contracts\DetailProcessors\DetailProcessor;
use RateHub\NewRelic\Contracts\Exceptions\ExceptionFilter;

final class ExceptionHandler implements IExceptionHandler
{
    /**
     * @var DetailProcessor
     */
    private $detailProcessor;

    /**
     * @var NewRelicAgentAdapter
     */
    private $newRelic;

    /**
     * @var ExceptionFilter
     */
    protected $exceptionFilter;

    public function __construct(DetailProcessor $detailProcessor, NewRelicAgentAdapter $newRelic, ExceptionFilter $exceptionFilter)
    {
        $this->detailProcessor = $detailProcessor;
        $this->newRelic = $newRelic;
        $this->exceptionFilter = $exceptionFilter;
    }

    public function report(Exception $e)
    {
        if ($this->exceptionFilter->shouldReport($e)) {
            $this->logException($e);
        }
    }

    public function render($request, Exception $e)
    {
        // Nothing to do for New Relic
    }

    public function renderForConsole($output, Exception $e)
    {
        // Nothing to do for New Relic
    }

    /**
     * Logs the exception to New Relic (if the extension is loaded)
     * Note: If you want some attributes ignored you have to add them
     * to the ini file under the field newrelic.attributes.exclude
     *
     * @param Exception $exception
     */
    protected function logException(Exception $exception)
    {
        $logDetails = $this->detailProcessor->__invoke([]);
        foreach ($logDetails as $param => $value) {
            $this->newRelic->addCustomParameter($param, $value);
        }

        $this->newRelic->noticeError($exception->getMessage(), $exception);
    }
}

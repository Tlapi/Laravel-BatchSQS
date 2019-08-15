<?php


namespace CoInvestor\BatchSQS\Queues\Exceptions;

use Exception;
use ArrayAccess;

class MessageSendingFailed extends Exception
{
    public $failures = [];

    public function __construct(ArrayAccess $failures)
    {
        $this->failures = $failures;
        parent::__construct($this->makeMessage());
    }

    protected function makeMessage()
    {
        $message = '';
        foreach ($this->failures as $failure) {
            $message .= "{$failure['Id']}: ({$failure['Code']}) {$failure['Message']}\n";
        }
        return $message;
    }
}

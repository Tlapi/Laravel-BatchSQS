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
    }

    public function __toString()
    {
        $message = '';
        foreach ($this->failures as $failure) {
            $message .= "{$failure['Id']}: ({$failure['Code']}) {$failure['Message']}\n";
        }
    }
}

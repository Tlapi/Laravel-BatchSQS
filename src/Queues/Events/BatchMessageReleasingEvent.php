<?php

namespace CoInvestor\BatchSQS\Queues\Events;

use Illuminate\Queue\SerializesModels;

class BatchMessageReleasingEvent
{
    use SerializesModels;

    public function __construct(string $queue, array $message)
    {
        $this->queue = $queue;
        $this->message = $message;
    }
}

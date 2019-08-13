<?php

namespace CoInvestor\BatchSQSQueue\Queues;

use Illuminate\Support\Facades\Event;
use CoInvestor\BatchSQSQueue\Queues\Traits\BatchQueueTrait;
use Illuminate\Queue\SqsQueue as IlluminateSqsQueue;
use CoInvestor\BatchSQSQueue\Queues\Events\BatchMessageReleasingEvent;

class SqsQueue extends IlluminateSqsQueue
{
    use BatchQueueTrait;
    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  array   $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->pushToBatch($payload, $queue, $options);
    }

    /**
     * https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#sendmessagebatch
     * @see BatchQueueTrait::releaseBatch()
     * @param array $batch An array of messages to be sent
     * @param string $queue
     * @return mixed
     */
    protected function releaseBatch(string $queue, array &$batch)
    {
        if (empty($batch)) {
            return null;
        }
        $messages = [
            'QueueUrl' => $this->getQueue($queue),
            'Entries' => [],
        ];
        foreach ($batch as $messageKey => $toSend) {
            $message = [
                'MessageBody' => $toSend['payload'],
            ];
            foreach (Event::dispatch(new BatchMessageReleasingEvent($queue, $message)) as $response) {
                $message = array_merge($message, $response);
            }
            $messages['Entries'][] = $message;
            unset($batch[$messageKey]);
        }
        return $this->sqs->sendMessageBatch($messages);
    }
}

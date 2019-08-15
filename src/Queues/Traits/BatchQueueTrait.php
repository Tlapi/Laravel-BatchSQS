<?php

namespace CoInvestor\BatchSQS\Queues\Traits;

trait BatchQueueTrait
{
    /**
     * @var array
     */
    protected $batches = [];

    /**
     * @var int The maximum size of a message batch. Defaults to 10. Messages will be released once a batch reaches this size.
     */
    protected $batchSize = 10;

    /**
     * Should send the array of payloads to the relevant queue driver (and unset each job in the batch array once it's been sent).
     * Array is structured: [['payload' => string, 'queue' => mixed|null, 'options' => array], ... ]
     * @param array $batch
     * @param string $queue The name of the queue that the batch should be sent to
     * @return mixed
     */
    abstract protected function releaseBatch(string $queue, array &$batch);

    /**
     * Adds a payload to the batch, and calls $this->releaseBatch() if the batch size limit has been reached.
     *
     * @param string $payload
     * @param $queue
     * @param array $options
     */
    protected function pushToBatch(string $payload, $queue = null, array $options = [])
    {
        $queue                   = $queue ?? 'default';
        $this->batches[$queue][] = ['queue' => $queue, 'payload' => $payload, 'options' => $options];
        if ($this->checkBatchSize($queue)) {
            $this->releaseBatch($queue, $this->batches[$queue]);
            unset($this->batches[$queue]);
        }
    }

    /**
     * Checks whether the current batch has exceeded the configured batch size
     * @param string $queue
     * @return bool
     */
    protected function checkBatchSize(string $queue): bool
    {
        return count($this->batches[$queue]) >= $this->batchSize;
    }

    public function setBatchSize(int $size)
    {
        $this->batchSize = $size;
    }

    public function __destruct()
    {
        if (!empty($this->batches)) {
            // During tests, the application gets refreshed, facades get re-initialised and this destructor gets called with the app in a half-recreated state
            $skipExecution = ($GLOBALS['refreshingApplication'] ?? false);

            $this->flush($skipExecution);
        }
    }

    /**
     * Flush all queues
     *
     * @param bool $skipExecution If set, jobs will simply be deleted without being executed
     */
    public function flush(bool $skipExecution = false)
    {
        if ($skipExecution) {
            $this->batches = [];
        } else {
            foreach ($this->batches as $queue => &$batch) {
                $this->releaseBatch($queue, $batch);
            }
        }
    }
}

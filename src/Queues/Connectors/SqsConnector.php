<?php

namespace CoInvestor\BatchSQS\Queues\Connectors;

use Aws\Sqs\SqsClient;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Queue\Queue;
use CoInvestor\BatchSQS\Queues\SqsQueue;
use Illuminate\Queue\Connectors\SqsConnector as IlluminateSqsConnector;

class SqsConnector extends IlluminateSqsConnector
{

    /**
     * @var array Defines the names of extra config directives which can be set in queue.php
     */
    protected $extraConfigDirectives = ['batch_size'];

    /**
     * Establish a queue connection.
     *
     * @param array $config
     * @return Queue
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);
        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        $queue = $this->applyExtraConfigDirectives(
            new SqsQueue(
                new SqsClient(Arr::except($config, $this->extraConfigDirectives)),
                $config['queue'],
                $config['prefix'] ?? ''
            ),
            $config
        );
    }

    /**
     * @param SqsQueue $queue
     * @param array $config
     * @return SqsQueue
     */
    protected function applyExtraConfigDirectives(SqsQueue $queue, array $config)
    {
        if (Arr::has($config, ['batch_size'])) {
            $queue->setBatchSize($config['batch_size']);
        }
        return $queue;
    }


}

<?php

namespace CoInvestor\BatchSQS\Queues\Connectors;

use Aws\Sqs\SqsClient;
use Illuminate\Support\Arr;
use CoInvestor\BatchSQS\Queues\SqsQueue;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Queue\Connectors\SqsConnector as IlluminateSqsConnector;

class SqsConnector extends IlluminateSqsConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return Queue
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);
        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }
        return new SqsQueue(
            new SqsClient($config),
            $config['queue'],
            $config['prefix'] ?? ''
        );
    }
}

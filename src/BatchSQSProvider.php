<?php
namespace CoInvestor\BatchSQSQueue;

use Illuminate\Support\ServiceProvider;
use CoInvestor\BatchSQSQueue\Queues\Connectors\SqsConnector;

class BatchSQSProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app["queue"]->addConnector("batch-sqs", function () {
            return new SqsConnector;
        });
    }

    public function register()
    {
    }
}
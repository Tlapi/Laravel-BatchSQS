<?php
namespace CoInvestor\BatchSQS;

use Illuminate\Support\ServiceProvider;
use CoInvestor\BatchSQS\Queues\Connectors\SqsConnector;

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

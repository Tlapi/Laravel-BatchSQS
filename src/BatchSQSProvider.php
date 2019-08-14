<?php
namespace CoInvestor\BatchSQS;

use Illuminate\Support\ServiceProvider;
use CoInvestor\BatchSQS\Queues\Connectors\SqsConnector;

class BatchSQSProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '../config/batch-sqs.php' => config_path('batch-sqs.php'),
        ]);
        $this->app["queue"]->addConnector("batch-sqs", function () {
            return new SqsConnector;
        });
    }

    public function register()
    {
    }
}

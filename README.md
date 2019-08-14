# Laravel-BatchSQS
SQS queue connector for Laravel which sends messages in batches.

## Installation

To install:

`composer require coinvestor/laravel-batchsqs`

Then to publish config files:

`artisan vendor:publish`

(select the BatchSQSProvider option when prompted)

## Usage

This package provides a queue connector called `sqs-batch`. Create a block inside `connections` as usual:

```php
'connections' => [
        'sqs-batch' => [
            'driver'      => 'sqs-batch',
            'key'         => env('AWS_KEY', null),
            'secret'      => env('AWS_SECRET', null),
            'prefix'      => env('AWS_PREFIX', null),
            'queue'       => env('AWS_QUEUE', null),
            'region'      => env('AWS_REGION', null),
        ],
// [...]
```

You can use all the usual Laravel SQS queue config options.

### Modifying messages before release

When a batch of messages is released to the queue, `CoInvestor\BatchSQS\Events\BatchMessageReleasingEvent` is dispatched.
To modify each message before it reaches the queue, simply define a listener which will inspect the message and then return 
an array which will be merged with the message before it is released:

```php
<?php
use \CoInvestor\BatchSQS\Queues\Events\BatchMessageReleasingEvent;
class BatchMessageReleasingEventListener
{
    public function handle(BatchMessageReleasingEvent $event)
    {
        $return = [];

        // If the message is 'foo' it should be changed to 'foobar'
        if ($event->message['MessageBody'] == 'foo') {
            $return['MessageBody'] = 'foobar';
        }
        // If the queue name ends in .norbert, the message should have a message group id consisting of 128 '1's
        if (preg_match('/.*\.norbert/', $event->queue)) {
            $return['MessageGroupId'] = str_repeat("1", 128);
        }

        return $return;
    }
}
```
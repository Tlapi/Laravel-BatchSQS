# Laravel-BatchSQS
SQS queue connector for Laravel which sends messages in batches.

## Installation

To install:

`composer require coinvestor/laravel-batchsqs`

Then to publish config files:

`artisan vendor:publish`

(select the BatchSQSProvider option when prompted)

## Usage

Set your queue driver (in `queue.php`) to `sqs-batch`.
You can use all the usual Laravel SQS queue config options.
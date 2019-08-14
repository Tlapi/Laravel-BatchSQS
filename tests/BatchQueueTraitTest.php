<?php

namespace CoInvestor\BatchSQS\tests;

use Faker\Factory as Faker;
use CoInvestor\BatchSQS\Queues\Traits\BatchQueueTrait;
use Orchestra\Testbench\TestCase;

class BatchQueueTraitTest extends TestCase
{
    protected $fakeQueue;
    protected $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker::create();

        $this->fakeQueue = new class
        {
            use BatchQueueTrait;

            public $releasedPayloads = [];

            public function pushRaw($payload, $queue = null, array $options = [])
            {
                $this->pushToBatch($payload, $queue);
            }

            public function releaseBatch(string $queue, array $batch)
            {
                foreach ($batch as $job) {
                    $this->releasedPayloads[$queue][] = $job['payload'];
                }
            }

            public function forgetWhatWasReleased()
            {
                $this->releasedPayloads = [];
            }
        };


        config(['batch-sqs.batch_size' => 10]);
    }


    public function testReleaseBatch()
    {
        $payloads = [];
        for ($i = 0; $i < 19; $i++) {
            $payloads[] = implode(' banana ', $this->faker->unique()->words(6));
        }

        // First batch of 10 should be released as soon as batch is complete
        for ($i = 0; $i < 10; $i++) {
            $this->fakeQueue->pushRaw($payloads[$i]);
            if ($i < 9) {
                $this->assertNotContains($payloads[$i], $this->fakeQueue->releasedPayloads['default'] ?? []);
            } else {
                $this->assertContains($payloads[$i], $this->fakeQueue->releasedPayloads['default'] ?? []);
            }
        }

        $this->fakeQueue->forgetWhatWasReleased();

        // Second batch of 9 should not be released yet
        for ($i = 10; $i < 19; $i++) {
            $this->fakeQueue->pushRaw($payloads[$i]);
            $this->assertNotContains($payloads[$i], $this->fakeQueue->releasedPayloads['default'] ?? []);
        }

        // Second batch of 9 should be released upon destruction
        $this->fakeQueue->__destruct();
        for ($i = 10; $i < 19; $i++) {
            $this->assertContains($payloads[$i], $this->fakeQueue->releasedPayloads['default'] ?? []);
        }
    }

    public function testMultipleQueues()
    {
        $payloads = [];
        for ($i = 0; $i < 29; $i++) {
            $payloads[] = implode(' banana ', $this->faker->unique()->words(6));
        }

        $queue1 ='1';
        // First batch of 10 should be released from queue 1 as soon as batch is complete
        for ($i = 0; $i < 10; $i++) {
            $this->fakeQueue->pushRaw($payloads[$i], $queue1);
            if ($i < 9) {
                $this->assertNotContains($payloads[$i], $this->fakeQueue->releasedPayloads[$queue1] ?? []);
            } else {
                $this->assertContains($payloads[$i], $this->fakeQueue->releasedPayloads[$queue1] ?? []);
            }
        }

        // Second batch of 10 should be released into queue 2 as soon as batch is complete
        $queue2 = '2';
        for ($i = 10; $i < 20; $i++) {
            $this->fakeQueue->pushRaw($payloads[$i], $queue2);
            if ($i < 19) {
                $this->assertNotContains($payloads[$i], $this->fakeQueue->releasedPayloads[$queue2] ?? []);
            } else {
                $this->assertContains($payloads[$i], $this->fakeQueue->releasedPayloads[$queue2] ?? []);
            }
        }

        // Third batch of 9 should be released into queue 3 upon destruction
        $queue3 = '3';
        for ($i = 20; $i < 29; $i++) {
            $this->fakeQueue->pushRaw($payloads[$i], $queue3);
            $this->assertNotContains($payloads[$i], $this->fakeQueue->releasedPayloads[$queue3] ?? []);
        }
        $this->fakeQueue->__destruct();
        for ($i = 20; $i < 29; $i++) {
            $this->assertContains($payloads[$i], $this->fakeQueue->releasedPayloads[$queue3] ?? []);
        }
    }
}

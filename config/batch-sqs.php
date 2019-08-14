<?php

return [
    // When the number of pending messages reaches batch_size, the messages will be sent to SQS
    'batch_size' => env('BATCH_SQS_BATCH_SIZE', 10),
];
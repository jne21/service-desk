<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class WriteTestLogJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $message,
    ) {
    }

    public function handle(): void
    {
        Log::info('WriteTestLogJob executed', [
            'message' => $this->message,
        ]);
    }
}
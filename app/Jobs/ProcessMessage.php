<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class ProcessMessage implements ShouldQueue
{
    use Queueable;

    public $message_id;

    public function __construct($message_id) {
        $this->message_id = $message_id;
    }

    public function handle(): void {

        //retreive message for processing
        $message = Message::find($this->message_id);

        if (!$message) {
            Log::warning("Message with ID {$this->message_id} not found.");
            return;
        }

        //process message
        Log::info('Processing message:', [
            'id' => $message->id,
            'message' => $message->message,
            'priority' => $message->priority,
            'type' => $message->type,
        ]);

        $message->update(['status' => 'done']);
    }
}

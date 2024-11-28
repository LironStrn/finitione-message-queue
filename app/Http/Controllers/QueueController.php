<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Jobs\ProcessMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class QueueController extends Controller {
    
    public function insertInstant(Request $request) {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'priority' => 'required|integer',
            'type' => 'required|string',
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false, 
                'errors' => $validator->errors()
            ], 422);
        }

        $message = Message::create([
            'message' => $request->message,
            'priority' => $request->priority,
            'type' => $request->type,
        ]);

        ProcessMessage::dispatch($message->id);

        Log::info('Message added to instant queue:', [
            'id' => $message->id,
            'message' => $message->message,
            'priority' => $message->priority,
            'type' => $message->type,
        ]);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    public function insertDelayed(Request $request) {

        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'priority' => 'required|integer',
            'type' => 'required|string',
            'delay' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $message = Message::create([
            'message' => $request->message,
            'priority' => $request->priority,
            'type' => $request->type,
            'delay' => $request->delay,
        ]);

        ProcessMessage::dispatch($message->id)->delay(now()->addSeconds($request->delay));

        Log::info('Message added to delayed queue:', [
            'id' => $message->id,
            'message' => $message->message,
            'priority' => $message->priority,
            'type' => $message->type,
            'delay' => $message->delay,
        ]);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    public function getQueueStatus() {
        $messages = Message::all();

        //get pending messages
        $pendingMessages = Message::where('status', 'pending')->get(['id', 'message', 'priority', 'type', 'created_at']);

        //get recently 10 messages processed 
        $recentProcessedMessages = Message::where('status', 'done')
        ->latest('updated_at')
        ->take(10)
        ->get(['id', 'message', 'priority', 'type', 'created_at', 'updated_at']);

        //count pending, processed and total messages
        $totalPending = $pendingMessages->count();
        $totalProcessed = Message::where('status', 'done')->count(); 
        $totalMessages = $totalPending + $totalProcessed;

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_messages' => $totalMessages,
                    'pending' => $totalPending,
                    'total_processed' => $totalProcessed, 
                ],
                'pending_messages' => $pendingMessages,
                'recent_processed_messages' => $recentProcessedMessages,
            ],
        ]);
    }
}

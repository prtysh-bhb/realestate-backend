<?php

namespace App\Http\Controllers\Api\Agent;

use App\Events\IsTypingEvent;
use App\Events\SendMessageEvent;
use App\Http\Controllers\Controller;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => ['required', 'exists:users,id'],
            'type'        => ['required', 'in:text,image,file,property,system'],
            'message'     => ['nullable', 'string'],
            'file'        => ['nullable', 'file', 'max:5120'], // 5MB
            'property_id' => ['nullable', 'exists:properties,id'],
            'meta'        => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(), // ⬅ FIRST ERROR ONLY
            ], 422);
        }

        $message = $this->messageService->createMessage($validator->validated(), $request->user());

        event(new SendMessageEvent($message->receiver_id, $message->sender_id, $message->message));

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data'    => $message
        ]);
    }

    public function getConversation(Request $request, $userId)
    {
        // Validate userId
        if (!\App\Models\User::where('id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $messages = $this->messageService->getConversation(
            auth()->id(),
            $userId
        );

        return response()->json([
            'success' => true,
            'message' => 'Conversation fetched successfully',
            'data' => $messages
        ]);
    }

    public function getCustomerAgents(Request $request)
    {
        try {
            $agents = $this->messageService->getAgentsForCustomer($request->user()->id);

            return response()->json([
                'success' => true,
                'agents' => $agents
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAgentCustomers(Request $request)
    {
        try {
            $customers = $this->messageService->getCustomersForAgent($request->user()->id);

            return response()->json([
                'success' => true,
                'customers' => $customers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function isTyping(Request $request){
        event(new IsTypingEvent($request->receiver_id, $request->user()->id));
    }

    public function markAsRead(Request $request, $partnerId)
    {
        try {
            $updated = $this->messageService->markMessagesAsRead(
                $request->user()->id,
                $partnerId
            );

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read.',
                'updated_count' => $updated
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getUnreadMessageCounts(Request $request)
    {
        try {
            $user = $request->user();

            // Get unread groups from service
            $unread = $this->messageService->getUnreadCountsForUser($user->id);

            // Convert to key-value array
            $result = $unread->mapWithKeys(function ($item) {
                return [
                    $item->user_id => $item->unread_count
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
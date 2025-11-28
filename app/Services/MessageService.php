<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class MessageService
{
    public function createMessage(array $data, $sender)
    {
        $payload = [
            'sender_id'   => $sender->id,
            'receiver_id' => $data['receiver_id'],
            'type'        => $data['type'],
            'message'     => $data['message'] ?? null,
            'property_id' => $data['property_id'] ?? null,
            'meta'        => $data['meta'] ?? null,
        ];

        // 🔹 Handle file upload (image or file)
        if (isset($data['file'])) {
            $file = $data['file'];

            $path = $file->store('messages', 'public');

            $payload['file_url']  = asset('storage/' . $path);
            $payload['file_name'] = $file->getClientOriginalName();
        }

        // 🔹 Create message
        $message = Message::create($payload);

        // 🔹 Broadcast event if needed (optional)
        // broadcast(new MessageSent($message))->toOthers();

        return $message->load(['sender', 'receiver', 'property']);
    }

    public function getConversation($userId, $otherUserId)
    {
        return Message::where(function ($q) use ($userId, $otherUserId) {
                $q->where('sender_id', $userId)
                  ->where('receiver_id', $otherUserId);
            })
            ->orWhere(function ($q) use ($userId, $otherUserId) {
                $q->where('sender_id', $otherUserId)
                  ->where('receiver_id', $userId);
            })
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getAgentsForCustomer($customerId)
    {
        // Find agent IDs where customer is either sender or receiver
        $agentIds = Message::where('sender_id', $customerId)
        ->orWhere('receiver_id', $customerId)
        ->get(['sender_id', 'receiver_id'])
        ->flatMap(function ($msg) use ($customerId) {
            // Return the opposite user in the conversation
            return [
                $msg->sender_id == $customerId ? $msg->receiver_id : $msg->sender_id
            ];
        })
        ->filter() // remove null or empty
        ->unique()
        ->values();

        // Fetch only agents
        return User::whereIn('id', $agentIds)
            ->where('role', 'agent')
            ->select('id', 'name', 'email', 'phone')
            ->get();
    }

    public function getCustomersForAgent($agentId)
    {
        // Get all user IDs that interacted with this agent
        $customerIds = Message::where('sender_id', $agentId)
            ->orWhere('receiver_id', $agentId)
            ->get(['sender_id', 'receiver_id'])
            ->flatMap(function ($msg) use ($agentId) {
                // Return the opposite user in the conversation
                return [
                    $msg->sender_id == $agentId ? $msg->receiver_id : $msg->sender_id
                ];
            })
            ->filter() // remove nulls
            ->unique()
            ->values();

        // Fetch only customers
        return User::whereIn('id', $customerIds)
            ->where('role', 'customer')
            ->select('id', 'name', 'email', 'phone')
            ->get();
    }

    public function markMessagesAsRead(int $userId, int $partnerId)
    {
        return Message::where('sender_id', $partnerId)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function getUnreadCountsForUser($userId)
    {
        return Message::where('receiver_id', $userId)
            ->whereNull('read_at')
            ->selectRaw('sender_id as user_id, COUNT(*) as unread_count')
            ->groupBy('sender_id')
            ->get();
    }
}

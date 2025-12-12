<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\AI\ChatbotService;
use Illuminate\Http\Request;

class AIChatbotController extends Controller
{
    protected $chatbot;

    public function __construct(ChatbotService $chatbot)
    {
        $this->chatbot = $chatbot;
    }

    /**
     * POST /api/customer/ai/chat
     * Send message to chatbot
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string',
        ]);

        $result = $this->chatbot->chat(
            $validated['message'],
            $validated['session_id'] ?? null,
            auth()->id()
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * GET /api/customer/ai/chat/history/{sessionId}
     * Get chat history
     */
    public function history($sessionId)
    {
        $result = $this->chatbot->getConversation($sessionId);

        return response()->json($result, $result['success'] ? 200 : 404);
    }
}
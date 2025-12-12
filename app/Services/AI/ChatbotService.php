<?php

namespace App\Services\AI;

use App\Models\AiConversation;
use App\Models\AiChatLead;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function chat($message, $sessionId = null, $userId = null)
    {
        try {
            Log::info('Chatbot message received', ['message' => $message, 'session' => $sessionId]);

            // Get or create conversation
            if ($sessionId) {
                $conversation = AiConversation::where('session_id', $sessionId)->first();
            }

            if (!isset($conversation)) {
                $conversation = AiConversation::create([
                    'user_id' => $userId,
                    'session_id' => Str::uuid(),
                    'type' => 'chatbot',
                    'messages' => [],
                    'extracted_data' => [],
                    'status' => 'active',
                ]);
            }

            // Get conversation history
            $history = $conversation->messages ?? [];

            // Build system prompt for first message
            if (empty($history)) {
                $systemMessage = "You are a friendly real estate assistant. Your goal is to help customers find properties and gather their requirements naturally. Ask about: budget, location, bedrooms, bathrooms, property type, and move-in timeline. Keep responses brief (2-3 sentences). Be conversational, not robotic.";
                
                $fullMessage = $systemMessage . "\n\nUser: " . $message . "\n\nRespond naturally and ask relevant follow-up questions.";
                
                $aiResponse = $this->gemini->generateText($fullMessage);
            } else {
                // Continue conversation with history
                $aiResponse = $this->gemini->chat($message, $history);
            }

            Log::info('Chatbot AI response', ['response' => $aiResponse]);

            if (!$aiResponse['success']) {
                return [
                    'success' => false,
                    'message' => 'Chatbot error',
                    'error' => $aiResponse['error'],
                ];
            }

            $botResponse = $aiResponse['text'];

            // Check if we should extract lead data (after 6+ messages)
            $extractedData = $conversation->extracted_data ?? [];
            $leadCaptured = false;

            if (count($history) >= 6 && empty($extractedData)) {
                // Try to extract lead data from conversation
                $leadData = $this->extractLeadFromConversation($history, $message, $userId);
                
                if ($leadData) {
                    $this->saveLead($conversation, $leadData, $userId);
                    $conversation->update([
                        'extracted_data' => $leadData,
                        'status' => 'completed',
                    ]);
                    $leadCaptured = true;
                    
                    // Add thank you message
                    $botResponse .= "\n\nThank you for sharing your requirements! Our team will contact you soon with suitable property options.";
                }
            }

            // Save messages
            $history[] = ['role' => 'user', 'content' => $message];
            $history[] = ['role' => 'assistant', 'content' => $botResponse];

            $conversation->update([
                'messages' => $history,
            ]);

            return [
                'success' => true,
                'response' => $botResponse,
                'session_id' => $conversation->session_id,
                'lead_captured' => $leadCaptured,
                'message_count' => count($history),
            ];

        } catch (\Exception $e) {
            Log::error('Chatbot error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    protected function extractLeadFromConversation($history, $latestMessage, $userId)
    {
        try {
            // Combine all messages
            $fullConversation = "";
            foreach ($history as $msg) {
                $fullConversation .= $msg['role'] . ": " . $msg['content'] . "\n";
            }
            $fullConversation .= "user: " . $latestMessage . "\n";

            // Ask AI to extract lead data
            $extractPrompt = "Extract customer information from this conversation and return ONLY valid JSON with these fields: name, email, phone, budget_min, budget_max, location_preference, property_type, bedrooms, bathrooms, move_in_date, additional_notes. Use null for missing fields. Return ONLY JSON, no other text.\n\nConversation:\n" . $fullConversation;

            $response = $this->gemini->generateJSON($extractPrompt);

            Log::info('Lead extraction response', ['response' => $response]);

            if ($response['success'] && !empty($response['data'])) {
                $data = $response['data'];
                
                // Validate that we have at least name or email or phone
                if (!empty($data['name']) || !empty($data['email']) || !empty($data['phone'])) {
                    return $data;
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Lead extraction error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function saveLead($conversation, $leadData, $userId)
    {
        // Calculate lead score (0-100)
        $score = $this->calculateLeadScore($leadData);

        AiChatLead::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'name' => $leadData['name'] ?? null,
            'email' => $leadData['email'] ?? null,
            'phone' => $leadData['phone'] ?? null,
            'budget_min' => $leadData['budget_min'] ?? null,
            'budget_max' => $leadData['budget_max'] ?? null,
            'location_preference' => $leadData['location_preference'] ?? null,
            'property_type' => $leadData['property_type'] ?? null,
            'bedrooms' => $leadData['bedrooms'] ?? null,
            'bathrooms' => $leadData['bathrooms'] ?? null,
            'move_in_date' => $leadData['move_in_date'] ?? null,
            'additional_notes' => $leadData['additional_notes'] ?? null,
            'lead_score' => $score,
            'status' => 'new',
        ]);

        Log::info('Lead saved', ['lead_data' => $leadData, 'score' => $score]);
    }

    protected function calculateLeadScore($data)
    {
        $score = 0;

        // Contact info (30 points)
        if (!empty($data['name'])) $score += 10;
        if (!empty($data['email'])) $score += 10;
        if (!empty($data['phone'])) $score += 10;

        // Budget (20 points)
        if (!empty($data['budget_min']) || !empty($data['budget_max'])) $score += 20;

        // Requirements (30 points)
        if (!empty($data['location_preference'])) $score += 10;
        if (!empty($data['property_type'])) $score += 10;
        if (!empty($data['bedrooms'])) $score += 10;

        // Timeline (20 points)
        if (!empty($data['move_in_date'])) $score += 20;

        return $score;
    }

    public function getConversation($sessionId)
    {
        $conversation = AiConversation::where('session_id', $sessionId)
            ->where('type', 'chatbot')
            ->first();

        if (!$conversation) {
            return [
                'success' => false,
                'message' => 'Conversation not found',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'session_id' => $conversation->session_id,
                'messages' => $conversation->messages ?? [],
                'status' => $conversation->status,
                'lead_captured' => !empty($conversation->extracted_data),
                'created_at' => $conversation->created_at,
            ],
        ];
    }
}
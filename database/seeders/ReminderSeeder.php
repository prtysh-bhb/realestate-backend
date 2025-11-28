<?php

namespace Database\Seeders;

use App\Models\Reminder;
use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReminderSeeder extends Seeder
{
    public function run()
    {
        $agent = User::where('role', 'agent')->first();
        $customer = User::where('role', 'customer')->first();
        $property = Property::where('agent_id', $agent->id)->first();
        $inquiry = Inquiry::where('agent_id', $agent->id)->first();

        if (!$agent || !$customer || !$property) {
            $this->command->warn('Please ensure you have at least 1 agent, 1 customer, and 1 property.');
            return;
        }

        $reminders = [
            // Overdue - High Priority
            [
                'agent_id' => $agent->id,
                'customer_id' => $customer->id,
                'inquiry_id' => $inquiry?->id,
                'property_id' => $property->id,
                'title' => 'Follow-up on property inquiry',
                'description' => 'Customer showed interest in 3BHK apartment',
                'type' => 'inquiry_followup',
                'priority' => 'high',
                'remind_at' => Carbon::yesterday()->setTime(10, 0),
                'status' => 'pending',
            ],
            
            // Due Today - Urgent
            [
                'agent_id' => $agent->id,
                'customer_id' => $customer->id,
                'title' => 'Send property documents',
                'description' => 'Customer requested documents for loan approval',
                'type' => 'document_pending',
                'priority' => 'urgent',
                'remind_at' => Carbon::today()->setTime(14, 0),
                'status' => 'pending',
            ],
            
            // Tomorrow - Medium
            [
                'agent_id' => $agent->id,
                'customer_id' => $customer->id,
                'property_id' => $property->id,
                'title' => 'Schedule second property viewing',
                'description' => 'Customer wants to bring family for second visit',
                'type' => 'appointment_followup',
                'priority' => 'medium',
                'remind_at' => Carbon::tomorrow()->setTime(11, 0),
                'status' => 'pending',
            ],
            
            // Next Week - Low
            [
                'agent_id' => $agent->id,
                'customer_id' => $customer->id,
                'title' => 'Check on payment status',
                'description' => 'Follow-up on token amount payment',
                'type' => 'payment_followup',
                'priority' => 'low',
                'remind_at' => Carbon::now()->addDays(5)->setTime(10, 0),
                'status' => 'pending',
            ],
            
            // Completed
            [
                'agent_id' => $agent->id,
                'customer_id' => $customer->id,
                'title' => 'Send property brochure',
                'description' => 'Sent detailed brochure via email',
                'type' => 'general',
                'priority' => 'medium',
                'remind_at' => Carbon::yesterday()->setTime(9, 0),
                'status' => 'completed',
                'completed_at' => Carbon::yesterday()->setTime(9, 30),
                'notes' => 'Brochure sent and received confirmation',
            ],
            
            // Snoozed
            [
                'agent_id' => $agent->id,
                'customer_id' => $customer->id,
                'title' => 'Discuss pricing negotiation',
                'description' => 'Customer wants to negotiate on final price',
                'type' => 'general',
                'priority' => 'high',
                'remind_at' => Carbon::now()->addDays(2)->setTime(15, 0),
                'status' => 'snoozed',
                'snoozed_until' => Carbon::now()->addDays(2)->setTime(15, 0),
            ],
        ];

        foreach ($reminders as $reminder) {
            Reminder::create($reminder);
        }

        $this->command->info('Sample reminders created successfully!');
    }
}
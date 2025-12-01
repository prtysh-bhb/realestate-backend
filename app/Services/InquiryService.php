<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\Property;
use App\Mail\PropertyInquiryConfirmationMail;
use Illuminate\Support\Facades\Mail;

class InquiryService
{
    public function createInquiry($customerId, $propertyId, $data)
    {
        $property = Property::where('id', $propertyId)
            ->where('status', 'published')
            ->where('approval_status', 'approved')
            ->first();

        if (!$property) {
            throw new \Exception('Property not found or not available');
        }

        $inquiry = Inquiry::create([
            'customer_id' => $customerId,
            'property_id' => $propertyId,
            'agent_id' => $property->agent_id,
            'customer_name' => $data['name'],
            'customer_email' => $data['email'],
            'customer_phone' => $data['phone'],
            'message' => $data['message'],
            'status' => 'new',
            'stage' => 'new',
        ]);

        if(env('APP_ENV') == 'production'){
            Mail::to($data['email'])->send(new PropertyInquiryConfirmationMail($inquiry->load('property', 'agent')));
        }

        return $inquiry;
    }

    public function getCustomerInquiries($customerId)
    {
        return Inquiry::with(['property:id,title,location,price,type', 'agent:id,name,email,avatar'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getAgentInquiries($agentId, $status = null, $stage = null)
    {
        $query = Inquiry::with(['property:id,title,location,price,type', 'customer:id,name,email,avatar'])
            ->where('agent_id', $agentId);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($stage) {
            $query->where('stage', $stage);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function updateInquiryStatus($inquiryId, $agentId, $status, $notes = null)
    {
        $inquiry = Inquiry::where('id', $inquiryId)
            ->where('agent_id', $agentId)
            ->first();

        if (!$inquiry) {
            throw new \Exception('Inquiry not found or you do not have permission');
        }

        $updateData = ['status' => $status];

        if ($notes) {
            $updateData['agent_notes'] = $notes;
        }

        if ($status === 'contacted' && !$inquiry->contacted_at) {
            $updateData['contacted_at'] = now();
        }

        $inquiry->update($updateData);
        return $inquiry;
    }

    public function getInquiryById($inquiryId, $userId, $userRole)
    {
        $query = Inquiry::with(['property', 'customer:id,name,email,avatar', 'agent:id,name,email,avatar'])
            ->where('id', $inquiryId);

        if ($userRole === 'customer') {
            $query->where('customer_id', $userId);
        } elseif ($userRole === 'agent') {
            $query->where('agent_id', $userId);
        }

        $inquiry = $query->first();

        if (!$inquiry) {
            throw new \Exception('Inquiry not found or you do not have permission');
        }

        return $inquiry;
    }
}
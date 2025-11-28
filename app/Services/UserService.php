<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getAllAgents($filters)
    {
        return User::where('role', 'agent')
            ->withCount('properties')
            ->select('id', 'name', 'email', 'phone', 'city', 'avatar', 'company_name', 
                     'license_number', 'is_active', 'two_factor_enabled', 'created_at')
            ->when(isset($filters['search']), function($q) use($filters){
                $q->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->when(isset($filters['status']), function($q) use($filters){
                $q->where('is_active', $filters['status']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getAllCustomers($filters)
    {
        return User::where('role', 'customer')
            ->select('id', 'name', 'email', 'phone', 'city', 'avatar', 
                     'is_active', 'two_factor_enabled', 'created_at')
            ->when(isset($filters['search']), function($q) use($filters){
                $q->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->when(isset($filters['status']), function($q) use($filters){
                $q->where('is_active', $filters['status']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getAgentById($id)
    {
        $agent = User::where('role', 'agent')
            ->where('id', $id)
            ->withCount('properties')
            ->first();

        if (!$agent) {
            throw new \Exception('Agent not found');
        }

        return $agent;
    }

    public function getCustomerById($id)
    {
        $customer = User::where('role', 'customer')
            ->where('id', $id)
            ->withCount(['inquiries', 'favorites'])
            ->first();
            info($customer->toArray());

        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        return $customer;
    }
}
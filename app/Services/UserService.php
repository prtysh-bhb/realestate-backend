<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getAllAgents()
    {
        return User::where('role', 'agent')
            ->select('id', 'name', 'email', 'is_active', 'two_factor_enabled', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getAllCustomers()
    {
        return User::where('role', 'customer')
            ->select('id', 'name', 'email', 'is_active', 'two_factor_enabled', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function getAgentById($id)
    {
        $agent = User::where('role', 'agent')
            ->where('id', $id)
            ->select('id', 'name', 'email', 'is_active', 'two_factor_enabled', 'created_at', 'updated_at')
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
            ->select('id', 'name', 'email', 'is_active', 'two_factor_enabled', 'created_at', 'updated_at')
            ->first();

        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        return $customer;
    }
}
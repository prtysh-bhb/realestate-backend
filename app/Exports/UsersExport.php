<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $role;
    protected $isActive;

    public function __construct($role = null, $isActive = null)
    {
        $this->role = $role;
        $this->isActive = $isActive;
    }

    public function collection()
    {
        $query = User::query();

        if ($this->role) {
            $query->where('role', $this->role);
        }

        if ($this->isActive !== null) {
            $query->where('is_active', $this->isActive);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Role',
            'Company Name',
            'License Number',
            'Address',
            'City',
            'State',
            'Zipcode',
            'Active',
            'Email Verified',
            'Created At',
            'Last Login',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->phone,
            $user->role,
            $user->company_name,
            $user->license_number,
            $user->address,
            $user->city,
            $user->state,
            $user->zipcode,
            $user->is_active ? 'Yes' : 'No',
            $user->email_verified_at ? 'Yes' : 'No',
            $user->created_at->format('Y-m-d H:i:s'),
            $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
        ];
    }
}
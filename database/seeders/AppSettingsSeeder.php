<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // GENERAL GROUP
            [
                'group' => 'GENERAL',
                'label' => 'Site Name',
                'name' => 'site_name',
                'value' => 'Real Estate Platform',
                'datatype' => 'string',
                'description' => 'Name of the website',
            ],
            [
                'group' => 'GENERAL',
                'label' => 'Site Status',
                'name' => 'site_status',
                'value' => 'active',
                'datatype' => 'string',
                'description' => 'Website status: active or maintenance',
            ],

            // CREDIT COSTS GROUP
            [
                'group' => 'CREDIT_COSTS',
                'label' => 'View Property Photos Cost',
                'name' => 'property_photo',
                'value' => '10',
                'datatype' => 'number',
                'description' => 'Credits required to view property photos',
            ],
            [
                'group' => 'CREDIT_COSTS',
                'label' => 'View Property Videos Cost',
                'name' => 'property_video',
                'value' => '15',
                'datatype' => 'number',
                'description' => 'Credits required to view property videos',
            ],
            [
                'group' => 'CREDIT_COSTS',
                'label' => 'View Agent Number Cost',
                'name' => 'agent_number',
                'value' => '10',
                'datatype' => 'number',
                'description' => 'Credits required to view agent contact number',
            ],
            [
                'group' => 'CREDIT_COSTS',
                'label' => 'Book Appointment Cost',
                'name' => 'book_appointment',
                'value' => '20',
                'datatype' => 'number',
                'description' => 'Credits required to book property visit appointment',
            ],
            [
                'group' => 'CREDIT_COSTS',
                'label' => 'View Exact Location Cost',
                'name' => 'exact_location',
                'value' => '5',
                'datatype' => 'number',
                'description' => 'Credits required to view exact property location',
            ],
            [
                'group' => 'CREDIT_COSTS',
                'label' => 'Unlock Documents Cost',
                'name' => 'unlock_documents',
                'value' => '25',
                'datatype' => 'number',
                'description' => 'Credits required to unlock property documents',
            ],
            [
                'group' => 'CREDIT_COSTS',
                'label' => 'Send Inquiry Cost',
                'name' => 'send_inquiry',
                'value' => '5',
                'datatype' => 'number',
                'description' => 'Credits required to send inquiry to property owner',
            ],
            [
                'group' => 'CREDIT_COSTS',
                'label' => 'Unlock VR Tour Cost',
                'name' => 'unlock_vr_tour',
                'value' => '30',
                'datatype' => 'number',
                'description' => 'Credits required to unlock VR/3D tour',
            ],
            [
                'group' => 'CREDIT_COSTS',
                'label' => 'View Analytics Cost',
                'name' => 'view_analytics',
                'value' => '15',
                'datatype' => 'number',
                'description' => 'Credits required to view property analytics',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('app_settings')->updateOrInsert(
                ['name' => $setting['name']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
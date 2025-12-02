<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Appointment;
use App\Models\Reminder;
use Carbon\Carbon;

class DefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        /* -----------------------------------------
         * 1. Create Users (1 admin, 2 agents, 2 customers)
         * ----------------------------------------- */

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Agent One',
                'email' => 'agent1@gmail.com',
                'role' => 'agent',
            ],
            [
                'name' => 'Agent Two',
                'email' => 'agent2@gmail.com',
                'role' => 'agent',
            ],
            [
                'name' => 'Customer One',
                'email' => 'customer1@gmail.com',
                'role' => 'customer',
            ],
            [
                'name' => 'Customer Two',
                'email' => 'customer2@gmail.com',
                'role' => 'customer',
            ],
        ];

        $createdUsers = [];

        foreach ($users as $u) {
            $createdUsers[$u['role']][] = User::create([
                'name'       => $u['name'],
                'email'      => $u['email'],
                'password'   => Hash::make('123456'),
                'role'       => $u['role'],
                'phone'      => fake()->phoneNumber(),
                'avatar'     => null,
                'bio'        => fake()->sentence(),
                'company_name'   => $u['role'] === 'agent' ? fake()->company() : null,
                'license_number' => $u['role'] === 'agent' ? strtoupper(Str::random(8)) : null,
                'address'    => fake()->address(),
                'city'       => fake()->city(),
                'state'      => fake()->state(),
                'zipcode'    => fake()->postcode(),
                'is_active'  => 1,
            ]);
        }

        $agents = $createdUsers['agent'];
        $customers = $createdUsers['customer'];


        /* -----------------------------------------
         * 2. Create 5-6 Properties for agents
         * ----------------------------------------- */

        $properties = [];

        for ($i = 1; $i <= 6; $i++) {
            $agent = $agents[array_rand($agents)];

            $properties[] = Property::create([
                'agent_id'       => $agent->id,
                'title'          => fake()->sentence(3),
                'description'    => fake()->paragraph(),
                'price'          => rand(100000, 900000),
                'location'       => fake()->country(),
                'address'        => fake()->address(),
                'city'           => fake()->city(),
                'state'          => fake()->state(),
                'zipcode'        => fake()->postcode(),
                'type'           => $i%2 == 0 ? 'rent' : 'sale',
                'property_type'  => 'apartment',
                'bedrooms'       => rand(1, 4),
                'bathrooms'      => rand(1, 3),
                'area'           => rand(600, 2000),
                'amenities'      => ['wifi', 'parking', 'security'],
                'images'         => [],
                'primary_image'  => null,
                'video'          => null,
                'documents'      => [],
                'status'         => 'published',
                'approval_status'=> 'approved',
                'approved_at'    => now(),
                'approved_by'    => $createdUsers['admin'][0]->id,
                'is_featured'    => rand(0, 1),
                'featured_until' => now()->addDays(rand(5, 30)),
            ]);
        }


        /* -----------------------------------------
         * 3. Create Inquiries
         * ----------------------------------------- */

        $inquiries = [];

        foreach ($customers as $customer) {
            foreach ($properties as $prop) {
                $inquiries[] = Inquiry::create([
                    'customer_id'    => $customer->id,
                    'property_id'    => $prop->id,
                    'agent_id'       => $prop->agent_id,
                    'customer_name'  => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                    'message'        => 'I am interested in this property.',
                    'status'         => 'new',
                    'stage'          => 'new',
                    'notes'          => null,
                    'history'        => [],
                ]);
            }
        }


        /* -----------------------------------------
         * 4. Create Appointments
         * ----------------------------------------- */

        $appointments = [];

        foreach ($inquiries as $inq) {
            $appointments[] = Appointment::create([
                'property_id'     => $inq->property_id,
                'agent_id'        => $inq->agent_id,
                'customer_id'     => $inq->customer_id,
                'inquiry_id'      => $inq->id,
                'type'            => 'call',
                'scheduled_at'    => Carbon::now()->addDays(rand(1, 10)),
                'duration_minutes'=> 30,
                'status'          => 'scheduled',
                'location'        => fake()->address(),
                'phone_number'    => fake()->phoneNumber(),
            ]);
        }


        /* -----------------------------------------
         * 5. Create Reminders
         * ----------------------------------------- */

        foreach ($appointments as $app) {
            Reminder::create([
                'agent_id'         => $app->agent_id,
                'customer_id'      => $app->customer_id,
                'inquiry_id'       => $app->inquiry_id,
                'property_id'      => $app->property_id,
                'appointment_id'   => $app->id,
                'title'            => 'Follow-up Reminder',
                'description'      => 'Follow up with customer after appointment.',
                'type'             => 'inquiry_followup',
                'priority'         => 'high',
                'remind_at'        => Carbon::now()->addDays(2),
                'status'           => 'pending',
                'notes'            => null,
                'email_sent'       => 0,
                'notification_sent'=> 0,
                'email_status'     => 'pending',
                'email_error'      => null,
            ]);
        }

        echo "Default data seeded successfully.\n";
    }
}

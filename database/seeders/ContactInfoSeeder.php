<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert or update the single record (assuming id 1 is the main contact info)
        \App\Models\ContactInfo::updateOrCreate(
            ['id' => 1],
            [
                'mobile_number' => '9876543210',
                'email' => 'contact@example.com',
                'whatsapp_number' => '9876543210',
            ]
        );
    }
}

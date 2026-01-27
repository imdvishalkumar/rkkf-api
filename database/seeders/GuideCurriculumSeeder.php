<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GuideCurriculum;

class GuideCurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds guide curriculum items with static PDF URLs.
     */
    public function run(): void
    {
        $this->command->info('Truncating guide_curricula table...');

        // Truncate the table
        GuideCurriculum::truncate();

        // Base URL for PDF files (production server storage path)
        $baseUrl = 'https://api.rkkf.imobiledesigns.cloud/storage/uploads/guide-curriculum/';

        $items = [
            [
                'title' => 'FAQ English',
                'subtitle' => null,
                'language' => 'English',
                'icon' => null,
                'file_url' => $baseUrl . 'FAQ_RKKF_ENG.pdf',
                'type' => 'faq',
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'FAQ Gujarati',
                'subtitle' => 'વારંવાર પૂછાતા પ્રશ્નો',
                'language' => 'Gujarati',
                'icon' => null,
                'file_url' => $baseUrl . 'FAQ_RKKF_GUJ.pdf',
                'type' => 'faq',
                'active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($items as $item) {
            GuideCurriculum::create($item);
            $this->command->info("  -> Created: {$item['title']} ({$item['language']})");
        }

        $this->command->info('');
        $this->command->info('Guide Curriculum seeded successfully!');
        $this->command->info('');
        $this->command->warn('Make sure PDF files are uploaded to: public/uploads/guide-curriculum/');
        $this->command->info('  - FAQ_RKKF_ENG.pdf');
        $this->command->info('  - FAQ_RKKF_GUJ.pdf');
    }
}

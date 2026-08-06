<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achivements;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'title'       => 'First Step',
                'description' => 'Complete your very first task on any roadmap.',
                'icon'        => 'emoji_events',
                // 'type'        => 'task_completion',
                'condition'   => ['count' => 1],
                'points'      => 10,
            ],
            [
                'title'       => 'On a Roll',
                'description' => 'Complete 5 tasks on your roadmap.',
                'icon'        => 'local_fire_department',
                // 'type'        => 'task_completion',
                'condition'   => ['count' => 5],
                'points'      => 25,
            ],
            [
                'title'       => 'Task Master',
                'description' => 'Complete 10 tasks total.',
                'icon'        => 'star',
                // 'type'        => 'task_completion',
                'condition'   => ['count' => 10],
                'points'      => 50,
            ],
            [
                'title'       => 'Halfway There',
                'description' => 'Reach 50% progress on your roadmap.',
                'icon'        => 'trending_up',
                // 'type'        => 'roadmap_progress',
                'condition'   => ['roadmap_id' => 1, 'percent' => 50],
                'points'      => 30,
            ],
            [
                'title'       => 'Road Complete',
                'description' => 'Finish a full career roadmap from start to end.',
                'icon'        => 'workspace_premium',
                // 'type'        => 'roadmap_completion',
                'condition'   => ['roadmap_id' => 1],
                'points'      => 100,
            ],
            [
                'title'       => 'Week Warrior',
                'description' => 'Stay active for 7 days in a row.',
                'icon'        => 'calendar_today',
                // 'type'        => 'streak',
                'condition'   => ['days' => 7],
                'points'      => 50,
            ],
            [
                'title'       => 'Profile Pro',
                'description' => 'Complete your profile with all required information.',
                'icon'        => 'person',
                // 'type'        => 'profile_complete',
                'condition'   => [],
                'points'      => 15,
            ],
        ];

        foreach ($achievements as $data) {
            Achivements::create($data);
        }

        $this->command->info('✓ Achievements seeded successfully!');
    }
}

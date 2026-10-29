<?php

namespace Database\Seeders;

use App\Models\Cycle;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(42);

        $user = User::updateOrCreate(
            ['email' => 'demo@guramismart.test'],
            ['name' => 'Pembudidaya Demo', 'password' => 'password']
        );
        $user->cycles()->delete();

        $active = $user->cycles()->create([
            'name' => 'Kolam A - Siklus Berjalan',
            'pond_area_m2' => 50,
            'seed_count' => 1000,
            'seed_size' => '5-7 cm',
            'stocking_date' => now()->subDays(90)->toDateString(),
            'initial_capital' => 5_000_000,
            'target_days' => 120,
        ]);
        $this->fillLogs($active, 90, 0);

        $done = $user->cycles()->create([
            'name' => 'Kolam B - Siklus Selesai',
            'pond_area_m2' => 40,
            'seed_count' => 800,
            'seed_size' => '5-7 cm',
            'stocking_date' => now()->subDays(160)->toDateString(),
            'initial_capital' => 4_000_000,
            'target_days' => 120,
        ]);
        $this->fillLogs($done, 120, 40);
        $done->harvest()->create([
            'harvest_date' => now()->subDays(40)->toDateString(),
            'total_weight_kg' => 320,
            'total_sales' => 11_200_000,
        ]);
        $done->forceFill(['status' => 'harvested', 'harvest_notified_at' => now()->subDays(40)])->save();
    }

    private function fillLogs(Cycle $cycle, int $days, int $endOffset): void
    {
        $feed = [];
        $water = [];
        $start = $cycle->stocking_date->copy();

        for ($d = 0; $d < $days; $d++) {
            $date = $start->copy()->addDays($d)->toDateString();
            $base = 1.0 + $d * 0.03;

            foreach (['pagi', 'sore'] as $session) {
                $feed[] = [
                    'fed_on' => $date,
                    'session' => $session,
                    'amount_kg' => round($base + mt_rand(-10, 10) / 100, 2),
                ];
            }

            $water[] = [
                'recorded_on' => $date,
                'temperature_c' => round(27.5 + mt_rand(-35, 35) / 10, 1),
                'ph' => round(7.2 + mt_rand(-9, 9) / 10, 1),
            ];
        }

        $cycle->feedLogs()->createMany($feed);
        $cycle->waterLogs()->createMany($water);
    }
}

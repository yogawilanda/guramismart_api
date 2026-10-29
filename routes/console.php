<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Models\Cycle;
use App\Models\User;
use App\Notifications\FeedReminder;
use App\Notifications\HarvestReminder;
use Illuminate\Support\Facades\Schedule;

Artisan::command('guramismart:feed-reminder {session}', function (string $session) {
    User::whereHas('cycles', fn ($q) => $q->where('status', 'active'))
        ->each(fn (User $u) => $u->notify(new FeedReminder($session)));
    $this->info("Pengingat pakan {$session} terkirim.");
});

Artisan::command('guramismart:harvest-reminder', function () {
    $count = 0;
    Cycle::with('user')->where('status', 'active')->whereNull('harvest_notified_at')
        ->each(function (Cycle $c) use (&$count) {
            if ($c->duration_days >= $c->target_days) {
                $c->user->notify(new HarvestReminder($c));
                $c->forceFill(['harvest_notified_at' => now()])->save();
                $count++;
            }
        });
    $this->info("{$count} pengingat panen terkirim.");
});

[$pagi, $sore] = config('guramismart.feed_reminder_times');
Schedule::command('guramismart:feed-reminder pagi')->dailyAt($pagi);
Schedule::command('guramismart:feed-reminder sore')->dailyAt($sore);
Schedule::command('guramismart:harvest-reminder')->dailyAt('06:00');

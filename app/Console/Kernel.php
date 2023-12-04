<?php

namespace App\Console;

use DateTime;
use App\Jobs\SendEmail;
use Illuminate\Support\Stringable;
use Illuminate\Console\Scheduling\Schedule;
use App\Infrastructure\Repository\SqlUserRepository;
use App\Infrastructure\Repository\SqlPeminjamanRepository;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $userSql = new SqlUserRepository();
            $peminjamanSql = new SqlPeminjamanRepository();
            $peminjaman = $peminjamanSql->getAllPeminjaman("SUCCESS");

            foreach ($peminjaman as $p) {
                $interval = new DateTime($p->getPaidAt());
                $interval = $interval->diff(new DateTime());
                $user = $userSql->find($p->getUserId());
                if ($interval->days > 3) {
                    SendEmail::dispatch($user->getEmail()->toString(), $p->getId()->toString());
                    $p->setStatus("EXPIRED");
                    $peminjamanSql->persist($p);
                }
            }
        })->everyFiveSeconds();

        $schedule->command('php artisan queue:work')->everySixHours();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

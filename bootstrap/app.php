<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan alias middleware Spatie Permission
        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Auto set Alpha setiap hari jam 08:05 (5 menit setelah jam absensi default tutup)
        $schedule->command('absensi:auto-alpha')->dailyAt('08:05');

        // Cek status WA sender setiap jam
        $schedule->command('wa:check-sender-status')->hourly();

        // Jalankan queue worker (opsional jika tidak pakai supervisor)
        // $schedule->command('queue:work --queue=notifikasi-wa --tries=3 --stop-when-empty')->everyMinute()->withoutOverlapping();
    })
    ->create();

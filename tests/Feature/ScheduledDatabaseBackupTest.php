<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ScheduledDatabaseBackupTest extends TestCase
{
    public function test_database_backup_configuration_is_private_encrypted_and_database_only(): void
    {
        $this->assertSame([], config('backup.backup.source.files.include'));
        $this->assertSame([], config('backup.backup.source.files.exclude'));
        $this->assertSame([config('database.default')], config('backup.backup.source.databases'));
        $this->assertSame(['backups'], config('backup.backup.destination.disks'));
        $this->assertSame('testing-only-backup-password', config('backup.backup.password'));
        $this->assertSame('default', config('backup.backup.encryption'));
        $this->assertSame(storage_path('app/private/backups'), config('filesystems.disks.backups.root'));

        $retention = config('backup.cleanup.default_strategy');

        $this->assertSame(7, $retention['keep_all_backups_for_days']);
        $this->assertSame(30, $retention['keep_daily_backups_for_days']);
        $this->assertSame(8, $retention['keep_weekly_backups_for_weeks']);
        $this->assertSame(12, $retention['keep_monthly_backups_for_months']);
        $this->assertSame(10240, $retention['delete_oldest_backups_when_using_more_megabytes_than']);
    }

    public function test_database_backup_and_cleanup_are_scheduled_without_overlap(): void
    {
        Artisan::call('list');

        $events = app(Schedule::class)->events();

        $backup = $this->findEvent($events, 'backup:run --only-db');
        $cleanup = $this->findEvent($events, 'backup:clean');

        $this->assertSame('30 1 * * *', $backup->expression);
        $this->assertSame('30 2 * * *', $cleanup->expression);
        $this->assertSame('Asia/Manila', $backup->timezone);
        $this->assertSame('Asia/Manila', $cleanup->timezone);
        $this->assertTrue($backup->withoutOverlapping);
        $this->assertTrue($cleanup->withoutOverlapping);
        $this->assertSame(storage_path('logs/scheduler.log'), $backup->output);
        $this->assertSame(storage_path('logs/scheduler.log'), $cleanup->output);
        $this->assertTrue($backup->shouldAppendOutput);
        $this->assertTrue($cleanup->shouldAppendOutput);
    }

    /**
     * @param  array<int, Event>  $events
     */
    private function findEvent(array $events, string $command): Event
    {
        $event = collect($events)->first(
            fn (Event $event): bool => str_contains($event->command ?? '', $command)
        );

        $this->assertInstanceOf(Event::class, $event, "Scheduled command [{$command}] was not found.");

        return $event;
    }
}

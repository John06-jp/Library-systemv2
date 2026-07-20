<?php

namespace App\Console\Commands;

use App\Support\PublicStoragePublisher;
use Illuminate\Console\Command;

class PublishPublicStorageCommand extends Command
{
    protected $signature = 'storage:publish
                            {--symlink-only : Only attempt symlink(), do not copy files}';

    protected $description = 'Expose storage/app/public at /storage (symlink or copy mirror for shared hosting)';

    public function handle(): int
    {
        if ($this->option('symlink-only')) {
            if (PublicStoragePublisher::trySymlink() && PublicStoragePublisher::isLinked()) {
                $this->info('Symlink created: public/storage → storage/app/public');

                return self::SUCCESS;
            }

            $this->warn('Could not create symlink (symlink() disabled or public/storage already exists).');

            return self::FAILURE;
        }

        PublicStoragePublisher::trySymlink();

        if (PublicStoragePublisher::isLinked()) {
            $this->info('public/storage is already linked to storage/app/public.');

            return self::SUCCESS;
        }

        $count = PublicStoragePublisher::publishAll();
        $this->info("Mirrored {$count} file(s) to public/storage (no symlink — OK for Hostinger).");

        return self::SUCCESS;
    }
}

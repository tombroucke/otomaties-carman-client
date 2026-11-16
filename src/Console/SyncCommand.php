<?php

namespace Otomaties\CarmanClient\Console;

use Illuminate\Console\Command;
use Otomaties\CarmanClient\Sync\Synchronizer;

class SyncCommand extends Command
{
    protected $signature = 'carman:sync';

    protected $description = 'Sync vehicles, options, accessories, prices and products from the Carman API';

    public function handle(Synchronizer $synchronizer): int
    {
        $this->components->info('Syncing data from Carman');

        $synchronizer->sync(function (string $postType, int $count) {
            $this->components->twoColumnDetail($postType, (string) $count);
        });

        $this->components->info('Sync completed');

        return self::SUCCESS;
    }
}

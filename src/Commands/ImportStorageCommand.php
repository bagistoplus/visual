<?php

namespace BagistoPlus\Visual\Commands;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Persistence\DatabaseThemeDataStore;
use BagistoPlus\Visual\Persistence\FileThemeDataStore;

class ImportStorageCommand extends CopyThemeDataCommand
{
    protected $signature = 'visual:storage:import
                            {--scope= : Copy only this storage scope, every scope when omitted}
                            {--theme= : Copy only this theme}
                            {--force : Replace theme data the database already holds}';

    protected $description = 'Copy Bagisto Visual theme data from files into the database';

    protected function source(): ThemeDataStore
    {
        return app(FileThemeDataStore::class);
    }

    protected function target(): ThemeDataStore
    {
        return app(DatabaseThemeDataStore::class);
    }
}

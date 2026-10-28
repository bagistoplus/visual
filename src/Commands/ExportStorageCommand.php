<?php

namespace BagistoPlus\Visual\Commands;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Persistence\DatabaseThemeDataStore;
use BagistoPlus\Visual\Persistence\FileThemeDataStore;

class ExportStorageCommand extends CopyThemeDataCommand
{
    protected $signature = 'visual:storage:export
                            {--scope= : The storage scope to copy, none when omitted}
                            {--theme= : Copy only this theme}
                            {--force : Replace theme data the files already hold}';

    protected $description = 'Copy Bagisto Visual theme data from the database into files';

    protected function source(): ThemeDataStore
    {
        return app(DatabaseThemeDataStore::class);
    }

    protected function target(): ThemeDataStore
    {
        return app(FileThemeDataStore::class);
    }
}

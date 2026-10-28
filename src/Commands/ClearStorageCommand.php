<?php

namespace BagistoPlus\Visual\Commands;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Persistence\FileThemeDataStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class ClearStorageCommand extends Command
{
    protected $signature = 'visual:storage:clear
                            {--force : Clear the storage path without confirmation}';

    protected $description = 'Clear the Bagisto Visual storage path';

    public function handle(): int
    {
        $path = FileThemeDataStore::dataPath();

        if (! $this->option('force') && ! confirm(
            label: 'Clear Bagisto Visual editor data? All unpublished and persisted work done in the Visual editor will be lost.',
            default: false,
            hint: 'This cannot be undone.',
        )) {
            warning('Bagisto Visual storage was not cleared.');

            return self::FAILURE;
        }

        if (config('bagisto_visual.storage') === 'database') {
            app(ThemeDataStore::class)->clear();

            info('Bagisto Visual theme data cleared from the database.');
        } elseif (File::isDirectory($path)) {
            app(ThemeDataStore::class)->clear();

            info("Bagisto Visual storage path cleared: {$path}");
        } else {
            info("Bagisto Visual storage path does not exist: {$path}");
        }

        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        info('Application view cache cleared.');
        info('Application cache cleared.');

        return self::SUCCESS;
    }
}

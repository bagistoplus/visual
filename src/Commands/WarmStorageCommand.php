<?php

namespace BagistoPlus\Visual\Commands;

use BagistoPlus\Visual\Persistence\DatabaseThemeDataStore;
use BagistoPlus\Visual\VisualManager;
use Illuminate\Console\Command;
use InvalidArgumentException;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class WarmStorageCommand extends Command
{
    protected $signature = 'visual:storage:warm
                            {--scope= : Warm only this storage scope, every scope when omitted}
                            {--theme= : Warm only this theme}';

    protected $description = 'Fill the cache with the published Bagisto Visual theme data kept in the database';

    public function handle(VisualManager $visual, DatabaseThemeDataStore $store): int
    {
        if (config('bagisto_visual.storage') !== 'database') {
            info('Bagisto Visual theme data is kept in files, so there is nothing to warm.');

            return self::SUCCESS;
        }

        $scope = $this->option('scope');

        if ($scope !== null) {
            try {
                $visual->ensureValidStorageScope($scope);
            } catch (InvalidArgumentException $e) {
                error($e->getMessage());

                return self::FAILURE;
            }
        }

        $published = $store->publishedThemes($scope, $this->option('theme'));

        if ($published === []) {
            info('No published theme data to warm.');

            return self::SUCCESS;
        }

        foreach ($published as $entry) {
            $count = $store->warm($entry['scope'], $entry['theme']);

            info(sprintf(
                'Warmed %s / %s: %d documents.',
                $entry['scope'] === '' ? 'no scope' : $entry['scope'],
                $entry['theme'],
                $count,
            ));
        }

        return self::SUCCESS;
    }
}

<?php

namespace BagistoPlus\Visual\Commands;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Persistence\ThemeSettingsCache;
use BagistoPlus\Visual\VisualManager;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Spatie\ResponseCache\Facades\ResponseCache;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

/**
 * Copy theme data from one store to another, whichever store the app is set
 * to use, within one storage scope.
 *
 * Each theme's editor documents replace the target's, and its live documents
 * are published there as a new version, so the target keeps its history.
 */
abstract class CopyThemeDataCommand extends Command
{
    abstract protected function source(): ThemeDataStore;

    abstract protected function target(): ThemeDataStore;

    public function handle(VisualManager $visual, ThemeSettingsCache $settingsCache): int
    {
        $scope = (string) $this->option('scope');

        try {
            $visual->ensureValidStorageScope($scope);
        } catch (InvalidArgumentException $e) {
            error($e->getMessage());

            return self::FAILURE;
        }

        return $visual->withStorageScope($scope, fn () => $this->copy($settingsCache));
    }

    protected function copy(ThemeSettingsCache $settingsCache): int
    {
        $source = $this->source();
        $target = $this->target();
        $held = $source->themes();
        $theme = $this->option('theme');

        if ($theme !== null && ! in_array($theme, $held, true)) {
            error("There is no data for theme [{$theme}] to copy.");

            return self::FAILURE;
        }

        $themes = $theme !== null ? [$theme] : $held;

        if ($themes === []) {
            info('There is no theme data to copy.');

            return self::SUCCESS;
        }

        $existing = array_values(array_intersect($themes, $target->themes()));

        if ($existing !== [] && ! $this->option('force')) {
            error('The target already holds data for: '.implode(', ', $existing).'. Use --force to replace it.');

            return self::FAILURE;
        }

        foreach ($themes as $code) {
            $snapshot = $source->snapshot($code);
            $target->restore($code, $snapshot);
            $settingsCache->forgetAll($code);

            info(sprintf(
                'Copied %s: %d editor documents, %s.',
                $code,
                count($snapshot->editor),
                $snapshot->live === null ? 'never published' : count($snapshot->live).' live documents',
            ));

            if ($snapshot->live === null && in_array($code, $existing, true)) {
                warning("{$code} was never published, so what the target serves for it was left as it was.");
            }
        }

        if (class_exists(ResponseCache::class)) {
            ResponseCache::clear();
        }

        return self::SUCCESS;
    }
}

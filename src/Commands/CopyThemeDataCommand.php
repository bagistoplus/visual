<?php

namespace BagistoPlus\Visual\Commands;

use BagistoPlus\Visual\Contracts\ThemeDataStore;
use BagistoPlus\Visual\Persistence\ThemeSettingsCache;
use BagistoPlus\Visual\Theme\ThemeCopies;
use BagistoPlus\Visual\VisualManager;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Spatie\ResponseCache\Facades\ResponseCache;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

/**
 * Copy theme data from one store to another, whichever store the app is set
 * to use, in every storage scope the source holds unless one is given.
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
        $scope = $this->option('scope');

        if ($scope !== null) {
            try {
                $visual->ensureValidStorageScope($scope);
            } catch (InvalidArgumentException $e) {
                error($e->getMessage());

                return self::FAILURE;
            }
        }

        $source = $this->source();
        $target = $this->target();
        $theme = $this->option('theme');
        $copies = [];
        $existing = [];

        foreach ($scope !== null ? [$scope] : $source->scopes() as $current) {
            $visual->withStorageScope($current, function () use ($source, $target, $theme, $current, &$copies, &$existing) {
                $held = $source->themes();
                $themes = $theme !== null ? array_values(array_intersect([$theme], $held)) : $held;
                $taken = $target->themes();

                foreach ($themes as $code) {
                    $copies[] = ['scope' => $current, 'theme' => $code];

                    if (in_array($code, $taken, true)) {
                        $existing[] = $this->label($current, $code);
                    }
                }
            });
        }

        if ($copies === []) {
            if ($theme !== null) {
                error("There is no data for theme [{$theme}] to copy.");

                return self::FAILURE;
            }

            info('There is no theme data to copy.');

            return self::SUCCESS;
        }

        if ($existing !== [] && ! $this->option('force')) {
            error('The target already holds data for: '.implode(', ', $existing).'. Use --force to replace it.');

            return self::FAILURE;
        }

        foreach ($copies as $copy) {
            $visual->withStorageScope($copy['scope'], function () use ($source, $target, $settingsCache, $copy, $existing) {
                $label = $this->label($copy['scope'], $copy['theme']);
                $snapshot = $source->snapshot($copy['theme']);
                $target->restore($copy['theme'], $snapshot);
                $settingsCache->forgetAll($copy['theme']);
                app(ThemeCopies::class)->forget();

                info(sprintf(
                    'Copied %s: %d editor documents, %s.',
                    $label,
                    count($snapshot->editor),
                    $snapshot->live === null ? 'never published' : count($snapshot->live).' live documents',
                ));

                if ($snapshot->live === null && in_array($label, $existing, true)) {
                    warning("{$label} was never published, so what the target serves for it was left as it was.");
                }
            });
        }

        if (class_exists(ResponseCache::class)) {
            ResponseCache::clear();
        }

        return self::SUCCESS;
    }

    protected function label(string $scope, string $theme): string
    {
        return ($scope === '' ? 'no scope' : $scope).' / '.$theme;
    }
}

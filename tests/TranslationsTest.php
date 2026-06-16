<?php

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

it('keeps supported locales aligned with the English translation keys', function () {
    $langPath = dirname(__DIR__).'/resources/lang';
    $englishPath = $langPath.'/en';

    $flatten = function (array $translations, string $prefix = '') use (&$flatten): array {
        $keys = [];

        foreach ($translations as $key => $value) {
            $translationKey = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                $keys = array_merge($keys, $flatten($value, $translationKey));

                continue;
            }

            $keys[] = $translationKey;
        }

        sort($keys);

        return $keys;
    };

    $translationFiles = collect(File::files($englishPath))
        ->map(fn (SplFileInfo $file) => $file->getBasename())
        ->sort()
        ->values()
        ->all();

    $locales = collect(File::directories($langPath))
        ->map(fn (string $directory) => File::basename($directory))
        ->reject(fn (string $locale) => $locale === 'en')
        ->sort()
        ->values()
        ->all();

    $missingTranslations = [];

    foreach ($locales as $locale) {
        foreach ($translationFiles as $translationFile) {
            $localizedFile = $langPath.'/'.$locale.'/'.$translationFile;

            if (! File::isFile($localizedFile)) {
                $missingTranslations[$locale][$translationFile][] = '[missing file]';

                continue;
            }

            $englishKeys = $flatten(require $englishPath.'/'.$translationFile);
            $localizedKeys = $flatten(require $localizedFile);
            $missingKeys = array_values(array_diff($englishKeys, $localizedKeys));

            if ($missingKeys !== []) {
                $missingTranslations[$locale][$translationFile] = $missingKeys;
            }
        }
    }

    $message = collect($missingTranslations)
        ->flatMap(fn (array $files, string $locale) => collect($files)
            ->map(fn (array $keys, string $file) => sprintf(
                '%s/%s: %s',
                $locale,
                $file,
                implode(', ', $keys)
            )))
        ->implode(PHP_EOL);

    expect($missingTranslations)->toBeEmpty($message);
});

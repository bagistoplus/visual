<?php

namespace BagistoPlus\Visual\Support;

use Craftile\Laravel\DiscoveryRoots;

class VisualDiscoveryFilter
{
    public function __construct(
        protected DiscoveryRoots $discoveryRoots
    ) {}

    public function allows(array $entry, string $type): bool
    {
        if (! isset($entry['class']) || ! is_string($entry['class'])) {
            return false;
        }

        return match ($type) {
            'block' => $this->classBelongsToRoots($entry['class'], $this->discoveryRoots->blocks()),
            'preset' => $this->classBelongsToRoots($entry['class'], $this->discoveryRoots->presets()),
            default => false,
        };
    }

    protected function classBelongsToRoots(string $class, array $roots): bool
    {
        $class = ltrim($class, '\\');

        foreach ($roots as $root) {
            if (! isset($root['namespace']) || ! is_string($root['namespace'])) {
                continue;
            }

            $namespace = trim($root['namespace'], '\\');

            if ($namespace === '') {
                continue;
            }

            if ($class === $namespace || str_starts_with($class, $namespace.'\\')) {
                return true;
            }
        }

        return false;
    }
}

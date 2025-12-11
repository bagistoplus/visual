<?php

namespace BagistoPlus\Visual\Contracts;

/**
 * Interface for blocks that support conditional visibility.
 * Blocks implementing this interface can define where they should be enabled or disabled.
 */
interface ConditionalBlockInterface
{
    /**
     * Define where this block should be enabled.
     *
     * @return array{regions?: string[], templates?: string[]}
     *
     * @example
     * return [
     *     'regions' => ['header', 'footer'],
     *     'templates' => ['index', 'products/*']
     * ];
     */
    public static function enabledOn(): array;

    /**
     * Define where this block should be disabled.
     * This takes precedence over enabledOn.
     *
     * @return array{regions?: string[], templates?: string[]}
     *
     * @example
     * return [
     *     'templates' => ['accounts/*', 'checkout/*']
     * ];
     */
    public static function disabledOn(): array;
}

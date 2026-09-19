<?php

namespace BagistoPlus\Visual\Support;

use Webkul\Core\Core;
use Webkul\Core\Facades\Core as CoreFacade;

/**
 * Remembers what the store's settings say for the lifetime of a request.
 *
 * Bagisto's SystemConfig keeps no memo, so a block that reads the same flag
 * once per rendered item pays for a query each time.
 */
class BagistoConfig
{
    /** @var array<string, mixed> */
    protected array $values = [];

    public function get(string $key, ?string $channel = null, ?string $locale = null): mixed
    {
        $memo = implode('|', [$key, $channel ?? '', $locale ?? '']);

        if (array_key_exists($memo, $this->values)) {
            return $this->values[$memo];
        }

        return $this->values[$memo] = $channel === null && $locale === null
            ? $this->core()->getConfigData($key)
            : $this->core()->getConfigData($key, $channel, $locale);
    }

    /**
     * @return Core
     */
    protected function core()
    {
        return CoreFacade::getFacadeRoot();
    }
}

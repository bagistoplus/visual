<?php

namespace BagistoPlus\Visual\Sections;

interface SectionInterface
{
    public static function getSchemaPath(): string;

    public static function getSchema(): array;
}

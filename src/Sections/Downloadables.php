<?php

namespace BagistoPlus\Visual\Sections;

class Downloadables extends BladeSection
{
    protected static array $enabledOn = ['account/downloadables'];

    protected static string $view = 'shop::sections.downloadables';
}

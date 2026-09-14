<?php

namespace BagistoPlus\Visual\Support;

use BladeUI\Icons\Factory;

/**
 * The markup the editor draws a section type's icon with.
 *
 * Bagisto names an icon by the class its own font is styled with, such as
 * `icon-list`, while the editor draws whatever markup a block hands it. The
 * classes Bagisto ships are answered with the matching Lucide icon, which the
 * editor already carries.
 *
 * A type that hands over markup of its own is left alone, so a premium section
 * can draw itself however it likes.
 */
class SectionIcons
{
    /**
     * @var array<string, string>
     */
    protected const MAP = [
        'icon-image' => 'lucide-image',
        'icon-product' => 'lucide-shopping-bag',
        'icon-folder' => 'lucide-folder',
        'icon-list' => 'lucide-list',
        'icon-cms' => 'lucide-file-text',
        'icon-store' => 'lucide-store',
    ];

    protected const FALLBACK = 'lucide-layout-grid';

    public function svg(?string $icon): string
    {
        $icon = trim((string) $icon);

        if (str_starts_with($icon, '<svg')) {
            return $icon;
        }

        return $this->render(self::MAP[$icon] ?? self::FALLBACK);
    }

    /**
     * An icon drawn as markup, or nothing at all when it cannot be found, so
     * that a missing icon costs a section its picture and not its place in the
     * editor.
     */
    protected function render(string $name): string
    {
        try {
            return app(Factory::class)->svg($name)->toHtml();
        } catch (\Throwable) {
            return '';
        }
    }
}

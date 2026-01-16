<?php

namespace BagistoPlus\Visual\Settings\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class TypographyValue
{
    public readonly ?FontValue $fontFamily;

    public readonly string $fontStyle;

    public readonly string $fontWeight;

    public readonly string|array $fontSize;

    public readonly string|array $lineHeight;

    public readonly string $letterSpacing;

    public readonly string $textTransform;

    public readonly string $id;

    public function __construct(array $data, string $id)
    {
        $this->id = $id;

        $this->fontFamily = $this->transformFontFamily($data['fontFamily'] ?? null);
        $this->fontStyle = $data['fontStyle'] ?? 'normal';
        $this->fontWeight = $data['fontWeight'] ?? '400';
        $this->fontSize = $data['fontSize'] ?? 'text-base';
        $this->lineHeight = $data['lineHeight'] ?? 'leading-normal';

        $this->letterSpacing = $data['letterSpacing'] ?? 'tracking-normal';
        $this->textTransform = $data['textTransform'] ?? 'none';
    }

    public function attributes(): HtmlString
    {
        $kebabId = $this->toKebabCase($this->id);

        return new HtmlString("data-typography=\"{$kebabId}\"");
    }

    public function toCss(?string $selector = null): HtmlString
    {
        $kebabId = $this->toKebabCase($this->id);
        $defaultSelector = "[data-typography=\"{$kebabId}\"]";
        $actualSelector = $selector ? "{$defaultSelector}, {$selector}" : $defaultSelector;

        $css = "{$actualSelector} {\n";

        if ($this->fontFamily) {
            $css .= "  --typography-font-family: '{$this->fontFamily}', sans-serif;\n";
        }

        $css .= "  --typography-font-style: {$this->fontStyle};\n";
        $css .= "  --typography-font-weight: {$this->fontWeight};\n";

        $defaultFontSize = $this->getDefaultValue($this->fontSize);
        $defaultLineHeight = $this->getDefaultValue($this->lineHeight);

        $css .= '  --typography-font-size: '.$this->convertFontSizeToCssValue($defaultFontSize).";\n";
        $css .= '  --typography-line-height: '.$this->convertLineHeightToCssValue($defaultLineHeight).";\n";
        $css .= '  --typography-letter-spacing: '.$this->convertLetterSpacingToCssValue($this->letterSpacing).";\n";
        $css .= "  --typography-text-transform: {$this->textTransform};\n";

        $css .= "}\n";

        $css .= $this->generateResponsiveMediaQueries($actualSelector);

        return new HtmlString($css);
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'fontFamily' => $this->fontFamily?->toArray(),
            'fontStyle' => $this->fontStyle,
            'fontWeight' => $this->fontWeight,
            'fontSize' => $this->fontSize,
            'lineHeight' => $this->lineHeight,
            'letterSpacing' => $this->letterSpacing,
            'textTransform' => $this->textTransform,
        ];
    }

    public function toHtml(): HtmlString
    {
        if (! $this->fontFamily) {
            return new HtmlString('');
        }

        return $this->fontFamily->toHtml();
    }

    private function transformFontFamily(string|array|null $fontFamily): ?FontValue
    {
        if (! $fontFamily) {
            return null;
        }

        $transformer = new FontTransformer;

        return $transformer($fontFamily);
    }

    private function getDefaultValue(string|array $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return $value['_default'] ?? $value[array_key_first($value)];
    }

    private function generateResponsiveMediaQueries(string $selector): string
    {
        $css = '';

        if (! is_array($this->fontSize) && ! is_array($this->lineHeight)) {
            return $css;
        }

        $breakpoints = [
            'mobile' => 'max-width: 639px',
            'tablet' => '(min-width: 640px) and (max-width: 1023px)',
            'desktop' => 'min-width: 1024px',
        ];

        foreach ($breakpoints as $breakpoint => $mediaQuery) {
            $hasFontSize = is_array($this->fontSize) && isset($this->fontSize[$breakpoint]);
            $hasLineHeight = is_array($this->lineHeight) && isset($this->lineHeight[$breakpoint]);

            if (! $hasFontSize && ! $hasLineHeight) {
                continue;
            }

            $css .= "\n@media ($mediaQuery) {\n";
            $css .= "  {$selector} {\n";

            if ($hasFontSize) {
                $css .= '    --typography-font-size: '.$this->convertFontSizeToCssValue($this->fontSize[$breakpoint]).";\n";
            }

            if ($hasLineHeight) {
                $css .= '    --typography-line-height: '.$this->convertLineHeightToCssValue($this->lineHeight[$breakpoint]).";\n";
            }

            $css .= "  }\n";
            $css .= "}\n";
        }

        return $css;
    }

    private function convertFontSizeToCssValue(string $size): string
    {
        $map = [
            'xs' => '0.75rem',
            'sm' => '0.875rem',
            'base' => '1rem',
            'lg' => '1.125rem',
            'xl' => '1.25rem',
            '2xl' => '1.5rem',
            '3xl' => '1.875rem',
            '4xl' => '2.25rem',
            '5xl' => '3rem',
            '6xl' => '3.75rem',
            '7xl' => '4.5rem',
            '8xl' => '6rem',
            '9xl' => '8rem',
        ];

        return $map[$size] ?? '1rem';
    }

    private function convertLineHeightToCssValue(string $lineHeight): string
    {
        $map = [
            'none' => '1',
            'tight' => '1.25',
            'snug' => '1.375',
            'normal' => '1.5',
            'relaxed' => '1.625',
            'loose' => '2',
        ];

        return $map[$lineHeight] ?? '1.5';
    }

    private function convertLetterSpacingToCssValue(string $letterSpacing): string
    {
        $map = [
            'tighter' => '-0.05em',
            'tight' => '-0.025em',
            'normal' => '0em',
            'wide' => '0.025em',
            'wider' => '0.05em',
            'widest' => '0.1em',
        ];

        return $map[$letterSpacing] ?? '0em';
    }

    private function toKebabCase(string $string): string
    {
        return Str::kebab($string);
    }
}

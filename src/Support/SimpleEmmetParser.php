<?php

namespace BagistoPlus\Visual\Support;

/**
 * This class provides a lightweight solution to convert simplified Emmet-like syntax into HTML markup.
 *
 * It supports the following key features of Emmet syntax:
 *
 * 1. **Tag Names**:
 *    - Standard HTML tags can be defined using just the tag name (e.g., div, header, span, etc.).
 *    - **Example**: `div` → `<div></div>`
 *
 * 2. **Classes**:
 *    - Classes can be specified using a dot (`.`) followed by the class name. Multiple classes can be chained together.
 *    - **Example**: `div.container` → `<div class="container"></div>`
 *    - **Example**: `div.container.box` → `<div class="container box"></div>`
 *
 * 3. **IDs**:
 *    - IDs are defined using a hash (`#`) followed by the ID value.
 *    - **Example**: `div#header` → `<div id="header"></div>`
 *
 * 4. **Text Content**:
 *    - Content that should be placed inside the tag can be defined using curly braces (`{}`).
 *      This content will be placed directly inside the tag.
 *    - **Example**: `div{Hello}` → `<div>Hello</div>`
 *    - **Example**: `div#header.container{Welcome}` → `<div class="container" id="header">Welcome</div>`
 *
 * 5. **Nesting**:
 *    - Elements can be nested using the greater-than (`>`) operator to define parent-child relationships.
 *    - **Example**: `div.container>header#main{Welcome}` → `<div class="container"><header id="main">Welcome</header></div>`
 *
 * Assumptions:
 * - The parser enforces that if both classes and IDs are defined, the ID must always come befor the class in the Emmet string.
 * - The supported Emmet syntax is basic and doesn't include advanced features like sibling operators (`+`) or attribute selectors (`[attr=value]`).
 *
 * Example Usages:
 * - `div#container.header{__content__}` → `<div id="container" class="header">__content__</div>`
 * - `div.container>header#id{__content__}` → `<div class="container"><header id="id">__content__</header></div>`
 */
class SimpleEmmetParser
{
    /**
     * Parses Emmet-like syntax and converts it to HTML markup.
     *
     * @param  string  $emmet  The Emmet-like string to be parsed.
     * @return string The resulting HTML string.
     */
    public static function parse(string $emmet): string
    {
        // Split the Emmet syntax into parts based on the '>' operator for nesting
        $parts = explode('>', $emmet);
        $html = '';
        $tagStack = [];

        foreach ($parts as $part) {
            // The regex matches the following Emmet syntax elements in order:
            // 1. (\w+): The tag name (e.g., div, header)
            // 2. (?:#([\w-_]+))?: Optionally matches an ID starting with '#' and followed by word characters or hyphens
            // 3. (?:\.([a-zA-Z0-9:_\.-]+))?: Optionally matches classes starting with '.' and. Supports :, _, ., -
            // 4. (?:\{(.+?)\})?: Optionally matches text content within curly braces
            preg_match('/^(\w+)(?:#([\w-]+))?(?:\.([a-zA-Z0-9:_\.-]+))?(?:\{(.+?)\})?$/', trim($part), $matches);

            if (empty($matches)) {
                continue;
            }

            $tag = $matches[1];
            $id = isset($matches[2]) ? $matches[2] : '';
            $classes = isset($matches[3]) ? str_replace('.', ' ', $matches[3]) : '';
            $text = isset($matches[4]) ? $matches[4] : '';

            $html .= "<$tag";
            if ($id) {
                $html .= " id=\"$id\"";
            }

            if ($classes) {
                $html .= " class=\"$classes\"";
            }

            $html .= '>';

            if ($text) {
                $html .= $text;
            }

            $tagStack[] = $tag;
        }

        // Closing the tags in reverse order to maintain proper nesting
        while ($tag = array_pop($tagStack)) {
            $html .= "</$tag>";
        }

        return $html;
    }
}

# Colors for Bagisto themes

Visual can [edit a theme whose sections Bagisto stores](/bagisto-themes/editing),
not only a visual theme. Such a theme has no settings file of its own, so its
colors are declared in its entry in `config/themes.php`.

## Declaring colors

```php
'default' => [
    'name' => 'Default',
    'customize' => [
        'editor' => 'visual',
        'colors' => [
            'brand' => [
                'label' => 'Brand',
                'default' => '#060C3B',
                'classes' => ['navyBlue'],
                'rules' => [
                    '.primary-button' => ['background-color', 'border-color'],
                ],
            ],
        ],
    ],
],
```

Each entry is keyed by the id the value is stored under. `label` is what the
editor shows, `default` is the value the theme was built with, `classes` names
the Tailwind color classes the theme was compiled with, and `rules` names any
other selector the color drives, with the properties it sets there.

A theme that declares no `colors` is given two: `brand`, mapped to `navyBlue`,
and `accent`, mapped to `lightOrange`. Those are the only brand colors the stock
theme's blades use. Its Tailwind config also names `darkGreen`, `darkBlue` and
`darkPink`, which appear in no markup, so offering them would mean offering
controls that change nothing.

## How a color reaches the storefront

Visual writes a stylesheet into the head of every storefront page, after the
theme's own, overriding the utilities each declared class was compiled into. For
a class named `navyBlue` that is:

| Selector | Property |
| --- | --- |
| `.text-navyBlue` | `color` |
| `.bg-navyBlue` | `background-color` |
| `.border-navyBlue` | `border-color` |
| `.ring-navyBlue` | `--tw-ring-color` |

Each of those is also written with the `hover:`, `focus:` and `checked:`
variants (`.hover\:bg-navyBlue:hover` and so on), because their compiled
selectors carry a pseudo class and would otherwise outweigh the plain override.

Utilities are not enough on their own. A component class built with `@apply`
carries the color in its own body, so the utility override never reaches it.
That is what `rules` is for, and it is why the built-in `brand` color also
drives the theme's buttons:

| Selector | Properties |
| --- | --- |
| `.primary-button` | `background-color`, `border-color` |
| `.secondary-button` | `color`, `border-color` |

Those two classes account for 66 buttons across the storefront.

## What this does not cover

Coverage is whatever the declared classes reach, and no more. The stock theme's
palette is literal hex in `tailwind.config.js`, compiled at build time into class
names spread across every blade, with no CSS custom property anywhere to
reassign. A color set here therefore does not touch:

- Grays, blacks and whites (`text-black`, `bg-white`, `text-zinc-500`), which the
  theme uses for text and surfaces everywhere, including chrome that was never
  meant to be themed.
- Hex written inline in a blade, in an SVG, or in a section's own custom CSS.
- Anything a merchant wrote into a static content section.
- The label color of a primary button, which stays white as the theme compiled
  it. A very light brand color therefore reads poorly on its own buttons.

Making this complete needs CSS custom properties in the theme itself, which is
proposed upstream.

## Publishing

Colors are held as drafts alongside the sections. The publish button counts them
as one change however many colors were edited, and the discard button puts back
what was last published.

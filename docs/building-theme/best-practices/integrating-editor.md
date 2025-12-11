# Integrating with the Visual Editor

Bagisto Visual includes a live preview editor that allows merchants to build and customize storefront pages interactively, without reloading the page. When a section or block is added, updated, or removed through the editor, its HTML is dynamically re-rendered from the backend and injected into the DOM in place—without triggering a full page reload.

However, any JavaScript behavior associated with sections or blocks (like carousels, modals, or event listeners) is not automatically re-initialized. Additionally, some setting changes—such as text, image URLs, or inline styles—can be updated instantly in the browser, without requiring a backend re-render.

This guide explains how to:

- Reinitialize JavaScript behavior when sections or blocks are re-rendered
- Enable instant, client-side updates for simple setting types
- Ensure blocks are visible and interactable when being edited

By integrating with these editor behaviors, your sections and blocks will feel fast, predictable, and intuitive to customize.

## 1. Reinitializing JavaScript

When a section or block is updated in the editor, its DOM is replaced. Any interactive JavaScript (like sliders or dropdowns) must be reattached.

Bagisto Visual emits events during this lifecycle:

### Common Events

| Event                   | Timing                                   | Use case                                                                                                                                                                    |
| ----------------------- | ---------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `visual:section:load`   | After section is added or re-rendered    | Re-run any necessary JavaScript to ensure the section functions and displays correctly, as if the page were freshly loaded. You may also want to restore the section state. |
| `visual:section:unload` | Before section is removed or re-rendered | Make sure to clean up event listeners, variables, and anything else to prevent issues when interacting with the page and avoid memory leaks. Also, save the section state.  |
| `visual:block:load`     | After block is added or re-rendered      | Re-run block-specific JavaScript behavior.                                                                                                                                  |
| `visual:block:unload`   | Before block is removed or re-rendered   | Clean up block-specific event listeners and state.                                                                                                                          |

### Lifecycle Events

| Event                           | Timing                        |
| ------------------------------- | ----------------------------- |
| `visual:section:adding`         | Before section is added       |
| `visual:section:added`          | After section is added        |
| `visual:section:removing`       | Before section is removed     |
| `visual:section:removed`        | After section is removed      |
| `visual:section:updating`       | Before section is updated     |
| `visual:section:updated`        | After section is updated      |
| `visual:section:moving`         | Before section is moved       |
| `visual:section:moved`          | After section is moved        |
| `visual:section:setting:updated`| When a section setting changes|
| `visual:block:adding`           | Before block is added         |
| `visual:block:added`            | After block is added          |
| `visual:block:removing`         | Before block is removed       |
| `visual:block:removed`          | After block is removed        |
| `visual:block:updating`         | Before block is updated       |
| `visual:block:updated`          | After block is updated        |
| `visual:block:moving`           | Before block is moved         |
| `visual:block:moved`            | After block is moved          |
| `visual:block:setting:updated`  | When a block setting changes  |

Each event exposes:

```ts
event.detail = {
  sectionId,  // Section ID
  section,    // Section object
  blockId,    // Block ID (when applicable)
  block,      // Block object (when applicable)
};
```

### Detect the Theme Editor

Use `@visual_design_mode` and `@end_visual_design_mode` directives to scope code that should only run in the Visual Editor live preview.

```blade
@visual_design_mode
    <!-- This code only runs in the editor -->
    <div class="editor-notice">You are in design mode</div>

    @pushOnce('scripts')
        <script>
            // JavaScript that only runs in the editor
            console.log('Editor mode active');
        </script>
    @endPushOnce
@end_visual_design_mode
```

This prevents editor-specific code from running on the live storefront, keeping your production code clean and performant.

### Section Example

```blade
@visual_design_mode
    @pushOnce('scripts')
        <script>
            document.addEventListener('visual:section:unload', (event) => {
                if (event.detail.section.type === '{{ $section->type }}') {
                    // Save scroll position, destroy carousels, etc.
                }
            });

            document.addEventListener('visual:section:load', (event) => {
                if (event.detail.section.type === '{{ $section->type }}') {
                    // Reinitialize interactivity: sliders, modals, listeners
                }
            });
        </script>
    @endPushOnce
@end_visual_design_mode
```

### Block Example

```blade
@visual_design_mode
    @pushOnce('scripts')
        <script>
            document.addEventListener('visual:block:unload', (event) => {
                if (event.detail.block.type === '{{ $block->type }}') {
                    // Clean up block-specific listeners, state, etc.
                }
            });

            document.addEventListener('visual:block:load', (event) => {
                if (event.detail.block.type === '{{ $block->type }}') {
                    // Reinitialize block-specific JavaScript
                }
            });
        </script>
    @endPushOnce
@end_visual_design_mode
```

If you are using `Alpine.js` or `Livewire`, your state will automatically persist between updates—no additional setup is needed. However, if you rely on vanilla JS or third-party libraries, you should reinitialize them after every update.

## 2. Enabling Instant Setting Updates

For simple updates (text, image URLs, classes), you can avoid full re-renders and apply changes directly in the DOM to provide instant preview without delay.

Bagisto Visual supports this via:

---

### Option 1: `liveUpdate()` Blade Directives

Use `$section->liveUpdate()` or `$block->liveUpdate()` to bind settings to elements.

These helpers inject metadata to enable the editor to update the live preview without requiring a server-side re-render.

#### `->text(string $settingId)`

**Updates the element's `textContent`** whenever the specified setting changes.

```blade
<h1 {{ $section->liveUpdate()->text('heading') }}>
  {{ $section->settings->heading }}
</h1>
```

#### `->html(string $settingId)`

**Updates the element's `innerHTML`** with the new setting value.

```blade
<div {{ $section->liveUpdate()->html('html_content') }}>
  {!! $section->settings->html_content !!}
</div>
```

#### `->outerHtml(string $settingId)`

**Replaces the entire element (`outerHTML`)** with the setting value.

```blade
<div {{ $section->liveUpdate()->outerHtml('html_block') }}>
  {!! $section->settings->html_block !!}
</div>
```

#### `->attr(string $settingId, string $attributeName)`

**Updates the specified HTML attribute** (e.g. `src`, `href`, `alt`) with the setting value.

```blade
<img
  src="{{ $section->settings->image }}"
  {{ $section->liveUpdate()->attr('image', 'src') }}>
```

#### `->style(string $settingId, string $property)`

**Updates a specific CSS style property** on the element using the setting value.

```blade
<div
  style="width: {{ $section->settings->width }}"
  {{ $section->liveUpdate()->style('width', 'width') }}>
</div>
```

#### 🔹 Multiple Bindings on a Single Element

You can bind multiple settings fluently to different parts of the same element:

```blade
<a
  href="{{ $section->settings->link_url }}"
  {{ $section->liveUpdate()
      ->text('link_text')
      ->attr('link_url', 'href') }}>
  {{ $section->settings->link_text }}
</a>
```

#### 🔹 Using with Blocks

The `liveUpdate()` method works with both sections and blocks:

**In a section:**
```blade
<h1 {{ $section->liveUpdate()->text('heading') }}>
  {{ $section->settings->heading }}
</h1>
```

**In a block:**
```blade
<p {{ $block->liveUpdate()->text('text') }}>
  {{ $block->settings->text }}
</p>
```

### Option 2: JavaScript API (`Visual.handleLiveUpdate()`)

For more complex cases (e.g. multiple targets, transform logic, or styling), use `Visual.handleLiveUpdate()`:

```blade
@visual_design_mode
    @pushOnce('scripts')
        <script>
            document.addEventListener('visual:editor:init', () => {
                window.Visual.handleLiveUpdate('{{ $section->type }}', {
                    // handle update of section level settings
                    section: {
                        title: { target: 'h2', text: true },
                        image: { target: 'img', attr: 'src' }
                    },
                    blocks: {
                        // handle update of block settings
                        announcement: {
                            text: { target: 'p', text: true }
                        }
                    }
                });
            });
        </script>
    @endPushOnce
@end_visual_design_mode
```

---

### API Reference: `handleLiveUpdate`

```ts
handleLiveUpdate(
  sectionType: string,
  mappings: {
    section?: Record<string, LiveUpdateOptions>;
    blocks?: Record<string, Record<string, LiveUpdateOptions>>;
  }
)
```

### LiveUpdateOptions

| Option      | Description                              |
| ----------- | ---------------------------------------- |
| `target`    | CSS selector within the section          |
| `text`      | Replace text content                     |
| `html`      | Replace inner HTML                       |
| `attr`      | Set a DOM attribute (e.g. `src`, `href`) |
| `style`     | Set a CSS style property                 |
| `handler`   | Custom JS function `(el, value) => {}`   |
| `transform` | Modify the value before applying it      |

## 3. Keep Edited Blocks Visible

When a merchant is editing a block, that block should remain visible — even if it's part of a carousel, tab, or other dynamic view.

**Best Practice:**

- When rendering blocks dynamically (e.g. in a slider), ensure the currently edited block is active or in view.
- This enhances clarity and ensures live changes are reflected immediately.

> You can detect which block is being edited using the `visual:section:updated` event and `event.detail`.

No JavaScript is strictly required, but your UI logic should accommodate visibility for active blocks.

## 4. Summary

- Use `@visual_design_mode` to scope editor-specific behavior
- Use `liveUpdate()` for simple instant updates
- Use `handleLiveUpdate()` for advanced DOM control
- Reinitialize JavaScript using `visual:section:load` and `visual:block:load`
- Make edited blocks clearly visible in the preview

These patterns help ensure your sections and blocks behave consistently and responsively within the live editor environment.

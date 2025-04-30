# Integrating with the Visual Editor

Bagisto Visual includes a live preview editor that allows merchants to build and customize storefront pages interactively, without reloading the page. When a section is added, updated, or removed through the editor, its HTML is dynamically re-rendered from the backend and injected into the DOM in place—without triggering a full page reload.

However, any JavaScript behavior associated with the section (like carousels, modals, or event listeners) is not automatically re-initialized. Additionally, some setting changes—such as text, image URLs, or inline styles—can be updated instantly in the browser, without requiring a backend re-render.

This guide explains how to:

- Reinitialize JavaScript behavior when a section is re-rendered
- Enable instant, client-side updates for simple setting types
- Ensure blocks are visible and interactable when being edited

By integrating with these editor behaviors, your sections will feel fast, predictable, and intuitive to customize.

## 1. Reinitializing JavaScript

When a section is updated in the editor, its DOM is replaced. Any interactive JavaScript (like sliders or dropdowns) must be reattached.

Bagisto Visual emits events during this lifecycle:

| Event                     | Timing                        | Use case                 |
| ------------------------- | ----------------------------- | ------------------------ |
| `visual:section:updating` | Before section is removed     | Save UI state            |
| `visual:section:updated`  | After new section is inserted | Reinitialize JS behavior |

Each event exposes:

```ts
event.detail = {
  type: string, // Section slug
  id: string, // Section instance ID
};
```

### Example

```blade
@visual_design_mode
    @pushOnce('scripts')
        <script>
            document.addEventListener('visual:section:updating', (event) => {
                if (event.detail.type === '{{ $section->type }}') {
                    // Save scroll position, destroy carousels, etc.
                }
            });

            document.addEventListener('visual:section:updated', (event) => {
                if (event.detail.type === '{{ $section->type }}') {
                    // Reinitialize interactivity: sliders, modals, listeners
                }
            });
        </script>
    @endPushOnce
@end_visual_design_mode
```

`@visual_design_mode` ensure that this code is only inject when the section is rendered in the editor live preview

If you are using `Alpine.js` or `Livewire`, your state will automatically persist between updates—no additional setup is needed. However, if you rely on vanilla JS or third-party libraries, you should reinitialize them after every update.

## 2. Enabling Instant Setting Updates

For simple updates (text, image URLs, classes), you can avoid full re-renders and apply changes directly in the DOM to provide instant preview without delay.

Bagisto Visual supports this via:

---

### Option 1: `liveUpdate()` Blade Directives

Use `$section->liveUpdate()` or `$block->liveUpdate()` to wire an element to a setting.

#### Text Content

```blade
<h2 {{ $section->liveUpdate('title') }}>
  {{ $section->settings->title }}
</h2>
```

#### Element Attributes

```blade
<img src="{{ $section->settings->image }}" {{ $section->liveUpdate('image', 'src') }}>
```

#### Inside Blocks

```blade
@foreach ($section->blocks as $block)
  <p {{ $block->liveUpdate('text') }}>
    {{ $block->settings['text'] }}
  </p>
@endforeach
```

These helpers inject the metadata to help the editor to update content without server side re-render.

---

### Option 2: JavaScript API (`handleLiveUpdate()`)

For more complex cases (e.g. multiple targets, transform logic, or styling), use `handleLiveUpdate()`:

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
- Reinitialize JavaScript using `visual:section:updated`
- Make edited blocks clearly visible in the preview

These patterns help ensure your section behaves consistently and responsively within the live editor environment.

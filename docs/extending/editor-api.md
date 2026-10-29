# Editor API and Events

The editor object gives your extension access to the page being edited, the current selection, the preview and the editor's events.

## Getting the Editor

Your entry file gets it from the `visual:editor:ready` event:

```js
document.addEventListener('visual:editor:ready', (event) => {
  const { editor } = event.detail;
});
```

Vue components you register as panels, header actions or modals receive it as the `editor` prop:

```vue
<script setup>
const props = defineProps({ editor: Object });
</script>
```

## Reading the Page

`editor.engine.getPage()` returns the page being edited. Its `blocks` object holds every block on the page, keyed by id:

```js
const page = editor.engine.getPage();

const reviews = Object.values(page.blocks).filter((block) => block.type === 'product-review');
```

Each block has an `id`, a `type` (the block or section type), its `properties` and its `children`.

`editor.getActiveBlock()` returns the selected block, or `undefined` when nothing is selected. Inside a Vue component it is reactive, so a `computed` follows the selection:

```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({ editor: Object });

const block = computed(() => props.editor.getActiveBlock());
</script>

<template>
  <p v-if="block">{{ block.type }}</p>
</template>
```

## Changing Blocks

`setBlockProperty()` changes one setting of a block. The preview updates and the change is saved to the draft like an edit made in the settings panel. `getBlockProperty()` reads one back:

```js
editor.setBlockProperty(blockId, 'heading', 'Summer sale');

editor.getBlockProperty(blockId, 'heading'); // 'Summer sale'
```

The value is stored as you pass it. Visual does not validate it against the setting, so pass what the setting's input would produce.

Wrap several changes in `batch()` to make them a single undo step:

```js
editor.batch(() => {
  editor.setBlockProperty(blockId, 'heading', 'Summer sale');
  editor.setBlockProperty(blockId, 'subheading', 'Up to 50% off');
});
```

## Selection and Notifications

```js
editor.ui.setSelectedBlock(blockId); // select a block and open its settings
editor.ui.clearSelectedBlock();

editor.ui.toast('Saved');
```

## Events

The editor has two event buses:

- `editor.engine` emits events when the page content changes.
- `editor.events` emits events when the editor UI changes.

Both have the same API. `on()` returns a function that removes the listener, and `once()` listens a single time:

```js
const stop = editor.engine.on('block:property:set', ({ blockId, key, value, oldValue }) => {
  console.log(`${blockId}.${key} changed from`, oldValue, 'to', value);
});

stop();
```

### Page Content Events

Listen to these on `editor.engine`:

| Event                | Payload                                                                      |
| -------------------- | ---------------------------------------------------------------------------- |
| `block:property:set` | `blockId`, `key`, `value`, `oldValue`                                        |
| `block:insert`       | `blockId`, `block`, `parentId`, `index`, `regionId`                          |
| `block:remove`       | `blockId`, `block`, `parentId`, `regionId`                                   |
| `block:move`         | `blockId`, `sourceIndex`, `sourceParentId`, `sourceRegionId`, `targetIndex`, `targetParentId`, `targetRegionId` |
| `block:duplicate`    | `originalBlockId`, `newBlockId`, `newBlock`, `parentId`, `index`, `regionId` |
| `block:toggle`       | `blockId`, `disabled`, `oldValue`                                            |
| `page:set`           | `previousPage`, `newPage`                                                    |
| `undo`, `redo`       | `command`                                                                    |

`page:set` fires when the editor loads a page, for example when you switch templates, channels or locales.

### Editor UI Events

Listen to these on `editor.events`:

| Event                       | Payload   |
| --------------------------- | --------- |
| `ui:block:select`           | `blockId` |
| `ui:block:clear-selection`  | none      |
| `ui:modal:open`             | `modalId` |
| `ui:modal:close`            | `modalId` |
| `inspector:enabled`         | none      |
| `inspector:disabled`        | none      |

```js
editor.events.on('ui:block:select', ({ blockId }) => {
  console.log('Selected', blockId);
});
```

## The Preview

The preview is a frame that reloads every time you open another page in the editor. Two methods let you react to it:

```js
editor.preview.onReady(() => {
  console.log('Preview loaded for the first time');
});

editor.preview.onDocumentReady(() => {
  console.log('A preview page loaded');
});
```

`onReady()` runs its callback once, the next time a preview page finishes loading. `onDocumentReady()` runs it after every preview page load. Register both inside your `visual:editor:ready` listener so they are in place before the first preview loads.

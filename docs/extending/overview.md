# Extending the Theme Editor

You can add your own tools to the theme editor: sidebar panels, tabs in the block settings panel, header buttons, modals, keyboard shortcuts and new setting types with their own input.

An extension has two parts:

- **PHP** tells the editor which script and stylesheet to load.
- **JavaScript** runs inside the editor page and registers your UI on the editor object.

Extensions load only on the editor page. They never reach the storefront or the preview frame.

## How an Extension Starts

1. The editor page renders. Visual prints your script and stylesheet tags right after the editor's own script.
2. The editor script creates the editor.
3. Your script runs and listens for the `visual:editor:ready` event.
4. Once the page is ready, the editor dispatches `visual:editor:ready` with the editor object in `event.detail.editor`. Your code registers its panels, buttons and fields there.

```js
document.addEventListener('visual:editor:ready', (event) => {
  const { editor } = event.detail;

  editor.ui.toast('My extension is loaded');
});
```

## What You Can Add

| To add                                    | Read                                                        |
| ----------------------------------------- | ----------------------------------------------------------- |
| Your script and stylesheet to the editor  | [Loading Editor Assets](./loading-assets.md)                 |
| Code that reads or changes blocks         | [Editor API and Events](./editor-api.md)                     |
| Sidebar panels, settings tabs, buttons    | [Editor UI](./editor-ui.md)                                  |
| Dialogs                                   | [Modals](./modals.md)                                        |
| A setting type with its own input         | [Custom Setting Types](./custom-setting-types.md)           |

## Vue or Plain DOM

Every place that renders UI accepts either a Vue component or a function that returns an `HTMLElement`. The examples in this section use Vue single file components, built with Vite, and each page shows the plain DOM form as well. Plain functions need no build step, which is handy for a quick tool.

::: warning Compatibility
The editor object is the same one the editor's own panels use. Its API can change between major versions of Bagisto Visual, so check the changelog when you upgrade.
:::

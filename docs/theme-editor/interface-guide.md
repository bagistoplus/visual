# Interface Guide

This page is a tour of the editor. Keep it open the first time you use the editor, and come back to it whenever you wonder what a button does.

![The four parts of the theme editor, numbered 1 to 4](/editor-guide/interface-areas.jpg)

The editor has four parts, numbered in the screenshot:

1. The **top bar**, with the page and language pickers, preview tools and the Publish button.
2. The **left panel**, which lists everything on the page and gives access to theme settings.
3. The **preview** in the middle, which shows your store as customers see it.
4. The **settings panel**, where you change the selected section or block. On wide screens it sits on the right; on smaller screens it opens over the left panel. See [On smaller screens](#on-smaller-screens).

## Top bar

<img src="/editor-guide/top-bar-left.png" alt="The left side of the top bar: back arrow, page selector and language selector" width="784">

<img src="/editor-guide/top-bar-right.png" alt="The right side of the top bar: device preview, select mode, undo, redo, preview and Publish" width="754">

From left to right (the numbers match the screenshots):

| # | Control | What it does |
| --- | --- | --- |
| 1 | Back arrow | Leaves the editor and returns to the Bagisto admin. Your draft is already saved. |
| 2 | Page selector | Chooses the page you are editing, such as Home page, Product Page or Cart Page. See [Pages & Templates](./pages-templates.md). |
| | Channel selector | Chooses the sales channel. It only appears when your store has more than one channel. |
| 3 | Language selector | Chooses the language you are editing. It only appears when the channel has more than one language. See [Languages & Channels](./languages-channels.md). |
| 4 | Device preview | Shows the preview at mobile, tablet, desktop or a custom width. |
| 5 | Select mode (cursor icon) | When it is on, clicking in the preview selects a block. Turn it off to use the preview like a normal store: open menus, follow links, try the cart. |
| 6 | Undo and redo | Steps backward or forward through your recent changes. |
| 7 | Preview (eye icon) | Opens your store in a new browser tab, with your unpublished changes. |
| 8 | Publish | Makes your draft live for customers. See [Preview & Publish](./publishing.md). |

## Left panel

The narrow rail on the far left switches what the left panel shows:

- **Page content** (stack icon) lists the sections and blocks of the current page.
- **Theme settings** (gear icon) holds the settings for your whole store: logo, colors, fonts and more. See [Theme Settings](./theme-settings.md).

### The page content tree

<img src="/editor-guide/layers.png" alt="The page content tree" width="321">

The tree mirrors the page from top to bottom. Click a name to select it: the preview scrolls to it and the settings panel opens. The numbers match the screenshot:

1. **Area names**: the tree is grouped into the **Header**, **Main** and **Footer** areas.
2. **Arrow**: click the small arrow to the left of a name to show the blocks inside it.
3. **Content hint**: the grey text after a name (for example *Free ...*) is the start of its content, so you can tell similar blocks apart.
4. **Add section** adds a new section to that area.
5. **Add block** adds a block inside the section or block just above it.
6. **Collapse**: the icon next to each area name collapses everything in that area, which helps on long pages.

Hover a row to show its eye icon (hide or show) and its trash icon (remove).

## Preview

The preview is your real storefront, rendered with your draft changes.

- Move the mouse over the page and an outline with a label shows which block is under the cursor.
- Click to select that block. Its settings open in the settings panel and its row is highlighted in the tree.
- A selected block shows a small toolbar:

<img src="/editor-guide/block-toolbar.png" alt="The block toolbar" width="133">

| Icon | Action |
| --- | --- |
| Up arrow | Move the block up (or left in a row). |
| Down arrow | Move the block down (or right in a row). |
| Copy | Duplicate the block with all its settings. |
| Crossed eye | Hide the block from the storefront. |
| Trash | Remove the block. |

::: tip Clicking through your store
To follow a link or open a menu inside the preview, turn off **Select mode** in the top bar first. When you reach a page the editor cannot customize, such as the login page, a message offers to take you back. See [Pages you cannot edit](./pages-templates.md#pages-you-cannot-edit).
:::

## Settings panel

When nothing is selected, the panel says **Select a block to edit its settings**. Once you select something, it shows:

- The name of the selection at the top, with an arrow to close the panel.
- A **⋮** menu with **Duplicate**, **Move to Previous**, **Move to Next**, **Disable** and **Remove**.
- The settings, grouped into sections such as **Default**, **Layout**, **Size**, **Appearance** and **Spacing**. Click a group name to open or close it.

<img src="/editor-guide/block-menu.png" alt="The block options menu" width="268">

Every change you make here appears in the preview right away. See [Editing Settings](./settings.md) for a description of each kind of field.

### On smaller screens

The editor adapts its layout to the width of your browser window:

| Browser window | Where the settings appear |
| --- | --- |
| Very wide (1920 pixels and more) | In their own column on the right, always visible. |
| Wide (1536 to 1919 pixels) | In a column on the right, while a section or block is selected. |
| Smaller, including most laptops | Over the left panel, in place of the page content tree. |

![On a smaller screen, the settings open over the left panel](/editor-guide/narrow-layout.jpg)

On a smaller screen, the editor works the same way; only the place changes:

1. Select a section or block in the preview or in the tree. Its settings replace the tree in the left panel.
2. Edit the settings. The preview stays visible next to them.
3. Click the **back arrow** (1 in the screenshot) at the top of the settings to return to the page content tree.

::: tip Get the side by side layout on a laptop
Zoom out the browser (Ctrl and minus, or Cmd and minus on a Mac) to make room. At 80 or 90 percent zoom, many laptop screens are wide enough to show the tree and the settings side by side. Press Ctrl + 0 (Cmd + 0 on a Mac) to go back to normal size.
:::

## Keyboard shortcuts

| Action | Windows and Linux | Mac |
| --- | --- | --- |
| Undo | Ctrl + Z | Cmd + Z |
| Redo | Ctrl + Y or Ctrl + Shift + Z | Cmd + Shift + Z |

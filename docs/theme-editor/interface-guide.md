# Interface Guide

The Visual Editor gives you full control over how your storefront looks — without writing any code. This page explains how to use the interface to add and arrange content visually.

## The Editor Layout

When you open the editor, you'll see three main areas:

### 1. Sidebar (left)

- Shows all the content on the page, broken into **sections**
- Sections are grouped as:
  - **Header & Footer**: Always visible on all pages
  - **Main Content**: Specific to the current page
- Click any section to open its settings

![The Visual Editor sidebar](/editor-sidebar.png)

---

### 2. Preview Area (center)

- Shows exactly how your storefront looks
- Updates instantly as you make changes
- Click items in the preview to edit their settings in the sidebar

![The Visual Editor section configuration panel](/editor-edit-section.png)

---

### 3. Toolbar (top)

- Shows which **page** you're editing (e.g. homepage, contact)
- Lets you **switch language** or **preview screen sizes**
- And a button to publish your edits

![The Visual Editor section configuration panel](/editor-toolbar.png)

## Switching Templates

Use the template selector in the toolbar to move between storefront templates. The selector keeps the same order as the theme defines, including separators between groups of templates.

Product, category, and CMS page templates open a second panel. That panel lists the default template, any custom templates for that type, and a **Create template** action.

![Custom template selector](/editor-custom-template-selector.png)

## Creating Custom Templates

From a product, category, or page template panel, click **Create template**.

![Custom template create modal](/editor-custom-template-create-modal.png)

The editor creates custom templates from a name and a base template:

- **Empty template** starts with an empty main content area.
- Existing templates can be duplicated as a starting point.
- Shared header and footer regions stay shared and are not copied into the custom template file.

After publishing, custom templates can be assigned to resources in the Bagisto admin.

## Assigning Custom Templates

Template assignment is optional. Enable it with `BAGISTO_VISUAL_TEMPLATE_ASSIGNMENTS=true` and run `php artisan migrate` before assigning custom templates in the Bagisto admin.

Custom templates are assigned from each resource edit form in Bagisto:

- **Product:** General panel. Product assignments are channel and locale specific.
- **Category:** Theme template accordion. Category assignments are locale specific.
- **CMS Page:** Theme template accordion. CMS page assignments are locale specific.

![Custom template assignment field](/editor-custom-template-admin.png)

CMS pages with an assigned custom page template also show Visual Editor shortcuts in the CMS page list and edit page.

## What You Can Do

- **Add Sections**
  Click “Add Section” to choose from available content blocks (e.g., banner, products, newsletter).

- **Reorder Sections**
  Drag and drop sections in the sidebar to change their order on the page.

- **Edit Section Settings**
  Click a section to change text, images, colors, layout options, and more.

- **Remove Sections**
  Click the ••• menu on a section and choose “Remove” to delete it.

- **Edit Blocks Inside a Section**
  Some sections include blocks — small repeatable items like feature cards, product tiles, or categories.

## Tips for Merchants

- Changes you make are **saved automatically**
- You can preview how your store looks on **desktop, tablet, and mobile**
- Not all sections are removable — headers and footers are fixed in most themes
- Each page (home, product, contact) may have different available content

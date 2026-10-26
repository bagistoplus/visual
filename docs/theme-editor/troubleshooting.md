# Troubleshooting

Answers to the questions merchants ask most often.

## My changes are not on the live store

Changes are saved as a draft and only go live when you click **Publish**. Open the editor, check your changes, and publish. If you just published, give a page cache a moment and reload. See [Preview & Publish](./publishing.md).

## I removed something by mistake

Click **Undo** in the top bar, or press Ctrl + Z (Cmd + Z on a Mac). Undo covers the changes made since you opened the editor. Next time, consider hiding instead of removing.

## The page content tree disappeared when I selected a block

On smaller screens, such as most laptops, the settings open over the left panel. Click the **back arrow** at the top of the settings to return to the tree. To see both side by side, zoom out the browser a little. See [On smaller screens](./interface-guide.md#on-smaller-screens).

## Clicking in the preview opens links instead of selecting

**Select mode** is off. Click the cursor icon in the top bar to turn it back on.

## Clicking in the preview selects blocks, but I want to open a menu or a product

Turn **Select mode** off, browse, and turn it on again when you want to edit.

## A message says "Page not editable"

You browsed to a page the editor cannot customize, such as the login or account pages. Click **Go to home page** or pick a page from the page selector. See [Pages you cannot edit](./pages-templates.md#pages-you-cannot-edit).

## I changed the header on one page and it changed everywhere

That is expected: the **Header** and **Footer** areas are shared by every page. Only the **Main** area belongs to a single page.

## Every product page shows the same product name, price or image

A product block has a product chosen in its **Product** field. Open the product template, select the block, and clear the **Product** field so it shows the product being viewed. See [Product pages](./pages-templates.md#previewing-with-a-real-product-or-category). The same applies to category blocks and their **Category** field.

## A section looks fine on desktop but broken on mobile

Switch the device preview to **Mobile**, select the section, and adjust the settings that have a screen icon: direction, height, alignment, padding. Changes made in this mode apply to mobile only. See [Settings that change per device](./settings.md#settings-that-change-per-device).

## My image is cropped badly on phones

Click **Edit metadata** under the image and move the focal point onto the important part. Check again on **Mobile**. See [Working with Images](./images.md#focal-point).

## A color I changed did not apply to one section

That section has its own **Color Scheme** chosen in **Appearance**. Either edit that scheme in **Theme Settings** > **Colors**, or switch the section to the scheme you edited.

## A new section is missing in another language

That language has its own structure because sections were added, removed or reordered in it. Switch to that language and add the section there too. See [Languages & Channels](./languages-channels.md).

## A text change in my default language did not reach the other languages

Once a language has been edited, it keeps its own texts. Switch to it and update the text there.

## I do not see "Create template"

Template assignments are not enabled on your store. Ask your developer to enable them. See [Custom Templates](./custom-templates.md).

## My custom template is not in the Theme template list in the admin

Publish it first: drafts do not appear in the admin. Also check you are on the right channel and language, because assignments are saved per language (and per channel for products).

## I assigned a custom template but the page still looks the same

- Check that the template is published and the item was saved after choosing it.
- For products, check the channel and language selected at the top of the product form when you assigned it.
- Custom templates only apply when the store uses a visual theme. They have no effect on Bagisto's own themes.

## When to ask a developer

The editor covers design and content. Ask a developer when you need:

- a new kind of section or block that does not exist in the pickers,
- a new setting on an existing block,
- a change to how checkout or the account pages behave,
- a new theme, or changes to the theme's code.

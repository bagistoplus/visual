# Duplicating Themes

Duplicating a theme gives you a copy you can customize without touching the theme your customers see. Work on a new design in the copy, preview it, and activate it on your channel when it is ready.

## What a copy is

A copy runs the same code as the theme it is based on: the same sections, blocks, templates and assets. What it does not share is your work. A copy has its own drafts, its own published version and its own theme settings, so editing one never changes the other.

When the theme package is updated, the copy gets the new code too, because it has no code of its own.

## Duplicating a theme

1. In the admin, go to **Bagisto Visual > Themes**.
2. Click **Duplicate** on the theme you want to copy.
3. Give the copy a name. A description is optional, and is a good place to note what the copy is for.
4. Click **Duplicate**.

The copy starts exactly as the original looks in the editor: its unpublished drafts and its published version are both copied. The version history is not.

A new copy is not active on any channel. Click **Customize** on it to open it in the editor.

You can also duplicate a copy. The new copy is based on the same theme package as the copy it was made from, so deleting one copy never breaks another.

## Going live with a copy

Activate the copy on a channel as you would any other theme: from Bagisto's theme gallery, or by choosing it as the channel's theme in **Settings > Channels**. Copies are listed there with their own name.

## Renaming and deleting a copy

Click **Edit** on a copy to change its name or description. The theme code it was given when it was created does not change.

Click **Delete** to remove a copy along with its drafts, its published version and its history. This cannot be undone. A copy that is active on a channel cannot be deleted: activate another theme on that channel first.

The theme packages themselves cannot be renamed or deleted from this page.

## What a copy shares with the original

- **Images and videos.** Uploaded media is shared by every theme, so deleting a copy does not delete any image.
- **Template assignments.** A product, category or page assigned to a custom template keeps that assignment whatever theme is active. If the active theme has no template under that name, the default template is used instead. The template field in the product, category and page forms only lists the templates of the theme active on the channel, so templates you create in a copy can be assigned once the copy is active.

## When the original theme is uninstalled

A copy whose theme package is no longer installed cannot be shown. It stays on the themes page marked as unavailable, with delete as the only action, and a channel set to it falls back to Bagisto's default theme. Its data is kept, so installing the package again brings the copy back.

## For theme developers

Callbacks registered with `whenActive` in a theme's service provider also run when a copy of the theme is active. The `Theme` they receive is the copy: its `code` is the copy's code, while its `basePath`, `vite` and settings schema are those of your package. `$theme->getParent()` returns your package's theme.

If your code uses the theme code to build view or translation namespaces, use the package's code instead:

```php
$this->whenActive(function (Theme $theme) {
    $packageCode = $theme->getParent()?->code ?? $theme->code;

    // ...
});
```

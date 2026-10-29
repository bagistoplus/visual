# Loading Editor Assets

The theme editor loads extra scripts and stylesheets that you register with the `ThemeEditor` facade. This page shows how to register them from your app, how to build them with Vite, and how to ship them from a theme or a package.

## Registering From Your App

Register your files in a service provider's `boot` method:

```php
use BagistoPlus\Visual\Facades\ThemeEditor;

public function boot(): void
{
    ThemeEditor::serving(function () {
        ThemeEditor::script(asset('build-editor/editor.js'));
        ThemeEditor::style(asset('build-editor/editor.css'));
    });
}
```

`ThemeEditor::serving()` runs its callback only when the editor page is served, so the storefront never pays for your files.

`script()` and `style()` take a URL or an array of URLs. Scripts are printed with `defer`, after the editor's own script. A string that starts with `<script` is printed as it is, which lets you add an inline script.

## Building With Vite

Keep the editor build apart from your storefront build with its own Vite config. Install the Vue plugin if your app does not have it yet:

```bash
npm install -D @vitejs/plugin-vue
```

Then create `vite.editor.config.js` at the root of your app:

```js
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  build: {
    outDir: 'public/build-editor',
    emptyOutDir: true,
    manifest: true,
    lib: {
      entry: 'resources/js/editor/index.js',
      formats: ['iife'],
      name: 'EditorExtension',
      fileName: () => 'editor.js',
      cssFileName: 'editor',
    },
    rollupOptions: {
      external: ['vue'],
      output: {
        globals: { vue: 'Vue' },
      },
    },
  },
});
```

And a script in `package.json`:

```json
{
  "scripts": {
    "build:editor": "vite build --config vite.editor.config.js"
  }
}
```

`npm run build:editor` writes `editor.js` and `editor.css` to `public/build-editor`.

A few settings in this config matter:

- **`external` and `globals`** make your components use the editor's own copy of Vue, which the editor exposes as `window.Vue`. Do not bundle a second copy. Components built against another copy of Vue cannot work inside the editor.
- **`formats: ['iife']`** builds one plain script, which is what `ThemeEditor::script()` prints.
- **`manifest: true`** is only needed when you load the build with `ThemeEditor::assets()`, shown below.

The file names carry no hash. If your server tells browsers to cache them for long, add a version to the URL, such as `asset('build-editor/editor.js').'?v=1.2.0'`.

## The Entry File

Your entry file imports your components and styles, then registers them once the editor is ready:

```js
// resources/js/editor/index.js
import NotesPanel from './NotesPanel.vue';
import './editor.css';

document.addEventListener('visual:editor:ready', (event) => {
  const { editor } = event.detail;

  editor.ui.registerSidebarPanel({
    id: 'store-notes',
    title: 'Store notes',
    render: NotesPanel,
  });
});
```

`visual:editor:ready` fires once per page load, so register everything inside one listener.

## Shipping From a Theme or a Package

A theme or a package usually publishes its build to `public/vendor/{name}`. `ThemeEditor::assets()` reads the Vite manifest in that directory and registers every script and stylesheet it lists:

```php
use BagistoPlus\Visual\Facades\ThemeEditor;

public function boot(): void
{
    $this->publishes([
        __DIR__.'/../public/build-editor' => public_path('vendor/acme-notes'),
    ], 'public');

    ThemeEditor::serving(function () {
        ThemeEditor::assets('vendor/acme-notes', '.vite/manifest.json');
    });
}
```

The first argument is the build directory, relative to `public`. The second is the manifest's path inside it. Vite writes the manifest to `.vite/manifest.json` by default.

`assets()` registers every file in the manifest, so keep the editor build in a directory of its own. When the manifest is missing, for example before the assets are published, nothing is loaded and no error is raised.

## Styling Your UI

The editor's stylesheet only holds the utility classes the editor itself uses. A class that happens to work today can disappear after an update. Style your UI with your own stylesheet and class names that will not clash with the editor's:

```css
/* resources/js/editor/editor.css */
.store-notes {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem;
}
```

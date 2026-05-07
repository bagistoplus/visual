# Changelog

All notable changes to `Bagisto Visual` will be documented in this file.

# [2.0.0-alpha.8](https://github.com/bagistoplus/visual/compare/v2.0.0-alpha.7...v2.0.0-alpha.8) (2026-05-07)


### Bug Fixes

* register cache middleware on app kernel ([3cf60b0](https://github.com/bagistoplus/visual/commit/3cf60b0758f80063c302b801b504784219d5e598))

# [2.0.0-alpha.7](https://github.com/bagistoplus/visual/compare/v2.0.0-alpha.6...v2.0.0-alpha.7) (2026-05-05)


### Bug Fixes

* **core:** register theme and channel morph aliases ([2571ba5](https://github.com/bagistoplus/visual/commit/2571ba55d63836617200906958fbee9d170ce839))
* **livewire:** support livewire 4.3 ([6e1b98d](https://github.com/bagistoplus/visual/commit/6e1b98d37a023e2a4b56aa336005f6efeb780131))
* **livewire:** use class names to render visual blocks ([2f4b4fd](https://github.com/bagistoplus/visual/commit/2f4b4fde960997d0aee58cd9f187a064713f7e3f))

# [2.0.0-alpha.6](https://github.com/bagistoplus/visual/compare/v2.0.0-alpha.5...v2.0.0-alpha.6) (2026-05-05)


### Features

* add image metadata support ([58bee1c](https://github.com/bagistoplus/visual/commit/58bee1c694c9b80709645739e4046f26cac91580))

# [2.0.0-alpha.5](https://github.com/bagistoplus/visual/compare/v2.0.0-alpha.4...v2.0.0-alpha.5) (2026-05-04)


### Bug Fixes

* forward encrypted session cookie to preview sub-request to prevent CSRF mismatch on database session driver ([9ae8a6c](https://github.com/bagistoplus/visual/commit/9ae8a6c39b0735ed1071b63020f4819cf7828d90))
* lazy-load Theme::$settings to prevent uninitialized property error in Livewire updates ([17cf477](https://github.com/bagistoplus/visual/commit/17cf47722c98d933fab74b72133fadb671b80199))
* refresh parent of repeated block on child reorder ([a9f175b](https://github.com/bagistoplus/visual/commit/a9f175bbfc61997b1b31055c0ff8c2435f6a1aa3))
* use regionId instead of regionName in block filter ([75e6f68](https://github.com/bagistoplus/visual/commit/75e6f6876d8b606b210de62656ce4e31370c724a))


### Features

* add data-morph-ignore support to morphdom handler ([05218ee](https://github.com/bagistoplus/visual/commit/05218ee161778b9adc29ed1208fa548acc0e523c))
* add discoverPresetsIn method ([ef45f05](https://github.com/bagistoplus/visual/commit/ef45f05c8e02d75579f5c056e6efe146d906c92e))
* add font weight and style support for typography presets based on selected font ([df048ea](https://github.com/bagistoplus/visual/commit/df048eae0ae3885365d088d5e940b75a28348d53))
* add inline rename support for typography presets ([6fce38b](https://github.com/bagistoplus/visual/commit/6fce38b91f48b2124af7de76608847763e2dde50))
* add isResponsiveValue and getResponsiveValue utilities to Visual ([a9ec37f](https://github.com/bagistoplus/visual/commit/a9ec37f002146ec21eb71fe5fedf2c968f719e10))
* add section alias for block in SimpleSection ([0007a28](https://github.com/bagistoplus/visual/commit/0007a28b1bb1f4a527dff1b8ee854721ef48817a))
* add shared Events interface for cross-package Livewire events ([916e7df](https://github.com/bagistoplus/visual/commit/916e7dfa5f63c4d1878f63d7ae0c53dcd75c16d6))
* allow 'auto' value in SpacingValue properties ([74af973](https://github.com/bagistoplus/visual/commit/74af97357ecc3d77e644d91112db98996aebade6))
* discover theme presets from provider ([fad128f](https://github.com/bagistoplus/visual/commit/fad128fd04c588115b4ac4262338dad8690d2c72))
* merge positions field in mergeUpdates ([19c5030](https://github.com/bagistoplus/visual/commit/19c5030d53afc8041f97bf49224abf00398250e6))

## v2.0.0-alpha.2 - 2025-12-30

### v2.0.0-alpha.2

#### Changes

##### Chores

- Update craftile/laravel version constraint from ^0.4.2 to ^0.4 to allow automatic updates to newer patch versions

#### Notes

This alpha release relaxes the craftile/laravel version constraint to enable automatic patch version updates.

## Release v2.0.0-alpha.1 - 2025-12-27

### Features

- Initialize editor with empty page when switching templates
- Make themes list responsive on mobile
- Configure NProgress loading indicators
- Add Spacing settings type for margin/padding control
- Improve editor persistence and publishing flow

### Bug Fixes

- Improve TemplateSelector menu positioning
- Keep ImagePicker dialog open after image selection
- Improve ImagePicker error handling
- Include parent children to render set when adding new blocks for live preview
- Handle null values in GradientPicker component
- Fix enable/disable rendering logic in the visual editor
- Fix blocks rendering logic in design mode

### Documentation

- Replace screenshot with video embed in theme editor overview
- Update requirements and demo link for v2

## v2.0.0-alpha - 2025-12-11

### 🎉 Bagisto Visual v2.0.0-alpha

We're excited to announce the first alpha release of **Bagisto Visual v2**! This is a complete rewrite featuring a new **blocks system** - independent, reusable components that transform how Bagisto themes are built.

#### ⚠️ Breaking Changes

This is a **major version** with breaking changes. v2 is **not compatible** with v1 themes and requires:

- PHP 8.2 or later
- Bagisto 2.3 or later

#### ✨ The Blocks System

v2 introduces a fundamental architectural shift: blocks are no longer scoped to individual sections. They're independent, reusable components shared across your entire theme.

**What This Means:**

- **Design Once, Use Everywhere**: Create a button or testimonial block and use it across your entire site. Update once, changes reflect everywhere.
- **Deep Nesting**: Build sophisticated layouts by nesting blocks inside blocks - columns inside tabs, galleries inside accordions.
- **Page Builder Capability**: Build completely different page layouts from the same sections by composing different blocks - minimal or rich, your choice.

#### 📦 Installation

```bash
# Ensure your project accepts dev packages
composer config minimum-stability dev && composer config prefer-stable true

# Install Bagisto Visual v2
composer require bagistoplus/visual:^2.0@dev

# Publish assets
php artisan vendor:publish --tag=visual-assets

# Install the default theme
composer require bagistoplus/visual-debut:^2.0@dev
php artisan vendor:publish --tag=visual-debut-assets



```
#### 🔗 Links

- [Documentation](https://visual.bagisto.plus/introduction/getting-started)

#### ⚡ Next Steps

This alpha release is ready for testing and development. Please report any issues on [GitHub Issues](https://github.com/bagistoplus/visual/issues).

We're working towards a stable v2.0.0 release with additional features, improved documentation, and comprehensive testing.


---

**Full Changelog**: https://github.com/bagistoplus/visual/compare/v1.0.0...v2.0.0-alpha

## v1.0.0 - 2025-12-11

### Bagisto Visual 1.0 release

It is finally time to tag the first stable release of Bagisto Visual.

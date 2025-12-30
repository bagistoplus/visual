# Changelog

All notable changes to `Bagisto Visual` will be documented in this file.

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

---
title: "Announcing Bagisto Visual v2: The Blocks System"
date: 2025-01-25
author: Bagisto Visual Team
excerpt: "Bagisto Visual v2 introduces the blocks system - shared, reusable components that transform how themes are built. Define blocks once, use them across multiple sections, update once and see changes everywhere."
---

Bagisto Visual v1 brought a visual theme editor and section-based architecture to Bagisto. However, it had one significant limitation: each section's repeatable content was scoped to that section alone. A button defined in your hero section couldn't be reused in your features section. A testimonial layout only worked in the testimonials section. Every section reinvented the wheel.

We're excited to announce **Bagisto Visual v2**, featuring **the blocks system** - a fundamental architectural shift that makes blocks independent, reusable components shared across multiple sections. Define a block once, use it everywhere.

## What's Available in v2

The blocks system introduces shared, reusable building blocks that sections can accept and compose:

**Core Capabilities**:
- **Define once, use everywhere**: Create a button block and use it in any section that accepts buttons
- **Update once, changes everywhere**: Modify a block definition and see updates across all sections using it
- **Flexible composition & deep nesting**: Sections accept blocks, blocks can contain other blocks, enabling page builder-style layouts
- **Reduced duplication**: No more defining the same components separately in multiple sections
- **Block libraries**: Build collections of reusable blocks for themes or agencies

**Available Block Types**:

**Content Blocks**:
- Heading, Paragraph, Rich Text, Quote, Spacer

**Media Blocks**:
- Image, Video, Icon, Gallery

**Interactive Blocks**:
- Button (multiple variants), Form Fields, Accordion, Tabs

**E-commerce Blocks**:
- Product Image, Product Title, Product Price, Product Rating, Product Labels, Product Description, Product Badges, Variant Selector, Add to Cart, Category Card

**Social Proof Blocks**:
- Testimonial, Review, Rating, Logo, Team Member

**Layout Blocks** (accept child blocks):
- Columns, Tabs, Accordion, Container, Divider

Developers can create custom blocks for specific needs.

## How the Blocks System Works

### Architecture Shift

**v1 (Scoped Content)**: Each section defined its own button properties separately. Update hero buttons? Feature buttons unchanged. No code reuse, no consistency enforcement.

**v2 (Shared Blocks)**: Define a Button block once with properties like text, URL, and style. That same block is accepted by Hero, Feature, CTA, Banner, and any other section. Update the block definition once, all instances across every section update automatically.

> 📸 **Screenshot needed**: Side-by-side comparison showing v1 (isolated button settings per section) vs v2 (shared Button block used across multiple sections)

### Deep Nesting: Page Builder Experience

Blocks in v2 aren't just reusable - they can contain other blocks, enabling deep nesting and sophisticated page layouts.

Container blocks like Columns, Tabs, and Accordion can accept child blocks. This means you can build complex structures: a Columns block containing a Heading, Paragraph, and Button in one column, and an Image with a nested Tabs block in another column. Inside those tabs, add more blocks - Gallery, Testimonials, anything you need.

**What deep nesting enables**:
- **Complex layouts**: Build multi-column designs with rich nested content
- **Nested containers**: Place tabs inside columns, accordions inside tabs, columns inside columns
- **Page builder experience**: Compose sophisticated structures visually in the editor
- **Granular control**: Edit at any nesting level - no code required

Merchants build these structures directly in the theme editor by adding blocks to container blocks, creating layouts as complex or simple as needed. This transforms the visual editor from a customization tool into a true page builder.

> 📸 **Screenshot needed**: Theme editor showing nested blocks - a Columns block containing child blocks (Heading, Paragraph, Button) in one column, and Image with nested Tabs in another. Editor sidebar shows the nested tree structure.

### Example: Custom Product Cards

Instead of a single rigid "Product Card" block, v2 provides granular product blocks that merchants can compose into completely custom product displays.

**Available product blocks**:
- Product Image (with zoom, lightbox options)
- Product Title (typography, link control)
- Product Price (styling, sale price display)
- Product Rating (stars, count, reviews link)
- Product Labels (Sale, New, Limited)
- Add to Cart Button
- Product Description
- Product Badges
- Variant Selector

**Three merchants, three completely different product cards**:

**Fashion Store**:
Large image → Sale badge → Product title → Price with strikethrough → Rating stars → Quick add button

**Electronics Store**:
Product title → Specs accordion → Image gallery → Rating with review count → Price → Compare button → Add to cart

**Handmade Marketplace**:
Image → "Handmade" badge → Artist name → Product title → Price → Material tags → Favorite button → Custom message option

Each merchant builds their perfect product card in the editor by adding, arranging, and styling individual product blocks. Change your mind? Rearrange blocks. Need to add a badge? Drop it in. Want reviews more prominent? Move the rating block up.

The same Product Grid section accepts these custom product cards, giving every store a unique product browsing experience - all without writing code or waiting for a developer.

> 📸 **Screenshot needed**: Three product card layouts side by side - Fashion (image-first), Electronics (specs-heavy), Handmade (story-focused) - showing how the same blocks create completely different displays.

### Sections as Containers

Sections in v2 act as flexible containers that accept specific block types. A Hero section might accept Heading, Paragraph, Button, Image, and Video blocks. Merchants compose sections by adding, arranging, and removing blocks from this menu. The same hero section can contain just a heading and button, or a video, multiple paragraphs, and three buttons - whatever the use case requires.

## Getting Started

### Installation

Install Bagisto Visual v2 via Composer:

```bash
composer require bagistoplus/visual:^2.0
php artisan vendor:publish --tag="visual-assets"
```

### Using Blocks in the Editor

1. Open any section in the visual theme editor
2. Click "Add Block" to view blocks this section accepts
3. Select a block type to add it to the section
4. Customize block properties in the settings panel
5. Drag blocks to reorder, delete blocks you don't need
6. Changes reflect in real-time preview

> 📸 **Screenshot needed**: Editor showing the "Add Block" interface with block picker displaying available blocks organized by category (Content, Media, E-commerce, etc.)

### Migrating from v1

v1 sections with scoped repeatable content can be refactored into v2 blocks. Extract repeatable content definitions into standalone blocks, update sections to accept these blocks, and both merchants and developers gain more flexibility and maintainability. See the [migration guide](https://visual.bagistoplus.com/migration) for detailed instructions.

## Building with Blocks

### Creating Custom Blocks

Blocks are PHP classes that extend base block types like `BladeBlock`, `LivewireBlock`, or `SimpleBlock`. Define the block's view template and configurable properties (text, links, images, styles, etc.). The same block can then be accepted by multiple sections, giving merchants the flexibility to use it wherever they need it.

### Ecosystem Opportunities

- **Theme developers**: Ship themes with rich block libraries, differentiate through unique block collections
- **Agencies**: Build internal block libraries for reuse across client projects
- **Marketplace**: Potential for community-contributed or premium block packs
- **Quality**: Proven, tested blocks improve theme reliability

## What's Next

The blocks system is the foundation. Here's what we're building:

- **Expanded block library**: Additional official blocks for common use cases
- **Enhanced nesting**: Even more container block types and improved nested block management
- **Conditional blocks**: Show/hide blocks based on rules or context
- **Block marketplace**: Community and premium block collections
- **Enhanced editor**: Improved block management, better drag-and-drop, more visual feedback
- **Performance**: Optimizations for faster editor loading and rendering

## Join the Conversation

The blocks system represents a new chapter for Bagisto theme development. We welcome your feedback, contributions, and ideas.

**Try v2**:
- Upgrade your installation and explore blocks
- Build a custom block for your use case
- Share feedback on what works and what doesn't

**For Developers**:
- Create and share custom blocks
- Contribute to the block library
- Help improve documentation and examples

**Connect**:
- **GitHub**: [github.com/bagistoplus/visual](https://github.com/bagistoplus/visual)
- **Documentation**: [visual.bagistoplus.com](https://visual.bagistoplus.com)
- **Issues**: [GitHub Issues](https://github.com/bagistoplus/visual/issues)

---

The blocks system makes Bagisto themes more composable, maintainable, and flexible. Shared blocks mean less code duplication, automatic consistency, and faster development. We're excited to see what you build with v2.

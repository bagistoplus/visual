---
title: 'Announcing Bagisto Visual v2: The Blocks System'
date: 2025-01-25
author: Bagisto Visual Team
excerpt: 'Bagisto Visual v2 introduces the blocks system - shared, reusable components that transform how themes are built. Define blocks once, use them across multiple sections, update once and see changes everywhere.'
---

Bagisto Visual v1 brought a visual theme editor and section-based architecture to Bagisto. However, it had one significant limitation: each section's repeatable content was scoped to that section alone. A button defined in your hero section couldn't be reused in your features section. A testimonial layout only worked in the testimonials section. Every section reinvented the wheel.

We're excited to announce **Bagisto Visual v2**, featuring **the blocks system** - a fundamental architectural shift that makes blocks independent, reusable components shared across multiple sections. Define a block once, use it everywhere.

![New visual editor](/visual-editor-v2.webp)

## What You Can Build Now

With v2, you can compose unique layouts from reusable blocks - no code required for merchants, maximum flexibility for developers.

**Design Once, Use Everywhere**
Create a custom button style and use it across your entire site - hero sections, product pages, banners, anywhere you need it. Update the style once, every instance updates automatically.

**Build Complex Layouts Visually**
Nest blocks inside blocks to create sophisticated page structures. Put columns inside tabs, accordions inside columns, galleries inside accordions - arrange content however makes sense for your store.

**Customize Without Limits**
The same Hero section that works for one page can be completely different on another - different blocks, different arrangement, different content. Your creativity is the only constraint.

v2 ships with a comprehensive block library covering content (Heading, Paragraph), media (Image, Gallery), interactive elements (Button, Accordion), complete e-commerce components (Product Image, Title, Price, Rating, Variants, Add to Cart), social proof (Testimonials, Reviews, Ratings), and layout containers (Container, Tabs, Accordion). Developers can create custom blocks for specific needs.

## Real Example: Design Your Perfect Product Card

Here's where blocks shine. Instead of a single rigid "Product Card" component, v2 gives you granular product blocks (Image, Title, Price, Rating, Labels, Add to Cart, Description, Badges, Variants) that merchants arrange into completely custom displays.

**Three stores, three completely different product cards from the same blocks:**

![Custom product cards](/visual-editor-custom-product-cards.webp)

Change your mind? Rearrange blocks in seconds. Need to add a badge? Drop it in. Want reviews more prominent? Drag the rating block up. Each merchant can create their own product card to match their brand - no two stores need to look the same, and no developer required.

## How It Works

**v1 approach:** Each section defined its own components separately. Build a button for Hero? Build it again for Features. Build it again for CTAs. No code reuse, no consistency.

**v2 approach:** Define a Button block once with properties (text, URL, style). That block works in Hero, Features, CTAs, Banners - anywhere that accepts buttons. Update it once, every instance updates everywhere.

Sections become flexible containers that accept specific block types. A Hero section might accept Heading, Paragraph, Button, Image, and Video blocks. Merchants compose exactly what they need by adding, arranging, and removing blocks. The same hero section can be minimal (heading + button) or rich (video + multiple paragraphs + three buttons) - whatever fits the page.

**Deep nesting unlocks page builder-style layouts:** Container blocks (Columns, Tabs, Accordion) accept child blocks. Build sophisticated structures like a Columns block containing Heading + Paragraph + Button in the left column, and Image with nested Tabs in the right column. Inside those tabs? Add Gallery blocks, Testimonials, anything you need. Merchants compose these structures directly in the editor - no code required.

![Deep nesting example](/visual-editor-deep-nesting.webp)

## Start Building Today

v2 represents a fundamental shift in how Bagisto themes work. Merchants gain unprecedented control over their storefronts. Developers ship faster and maintain easier. Agencies scale their operations more efficiently. Everyone wins.

Whether you're a store owner tired of compromising on design, a developer exhausted from rebuilding the same components, or an agency looking to improve margins - v2 gives you the tools to build exceptional storefronts without the traditional tradeoffs.

**Get Started:**

- **Download**: [Install v2](https://github.com/bagistoplus/visual) and start building
- **Learn**: [Read the documentation](https://visual.bagistoplus.com) with examples and guides
- **Connect**: [Join discussions](https://github.com/bagistoplus/visual/discussions) to share ideas and get help

---

_The blocks system makes theme customization accessible to everyone. Build your perfect storefront - no code required._

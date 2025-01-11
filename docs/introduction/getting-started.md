# Getting Started

## 1. **Installation Instructions**

To get started with **Bagisto Visual**, you first need to install it on your Bagisto project. Follow the steps below:

### Prerequisites

Before installing **Bagisto Visual**, make sure you have the following:

- PHP version 8.1 or later.
- A running Bagisto store (version 2.0.0 or later).

### Step 1: Install Bagisto Visual via Composer

In the root directory of your Bagisto project, run the following Composer command:

```bash
composer require bagistoplus/visual
```

### Step 2: Publish the assets.

```bash
php artisan vendor:publish --tag="bagistoplus-visual-assets"
```

### Step 3: Install the default visual theme

```bash
composer require bagistoplus/visual-debut
```

#### Set up the Theme

- Navigate to the Theme Settings in the Bagisto admin panel.
- Select the "Visual Debut" theme provided by Bagisto Visual

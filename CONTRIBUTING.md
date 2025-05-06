# Contributing to Bagisto Visual

Thank you for your interest in contributing to **Bagisto Visual**!
We welcome issues, suggestions, bug reports, and pull requests that improve the project.

## How to Contribute

### 1. Fork the Repository

Create your own fork of the repo and clone it locally:

```bash
git clone https://github.com/YOUR_USERNAME/visual.git
cd visual
```

### 2. Set Up the Project

Make sure you have a working Bagisto installation and that your local theme uses your development version of the package.

Install dependencies and link your fork locally if needed:

```bash
composer install
npm install
```

### 3. Create a Feature Branch

Name your branch based on the type of change:

```bash
git checkout -b fix/theme-editor-alignment
```

### 4. Make Your Changes

Write clear, consistent code. Follow the existing conventions.

If you’re adding a feature:

- Include documentation
- Add a code comment where necessary

If you’re fixing a bug:

- Include a test case or steps to reproduce if possible

### 5. Commit with a Clear Message

```bash
git add .
git commit -m "Fix: correct section layout misalignment"
```

### 6. Submit a Pull Request

Go to the main repo and submit your pull request from your fork.

> Include a short summary describing **what** you did and **why** it’s useful.

## Reporting Bugs & Requesting Features

1. [Open an issue](https://github.com/bagistoplus/visual/issues)
2. Describe the problem or idea clearly.
3. Include screenshots, code samples, or reproduction steps when helpful.

## Code Style & Guidelines

- PSR-12 coding standard for PHP
- Tailwind CSS for frontend
- Follow Blade and Livewire best practices
- Keep PRs focused — avoid mixing unrelated changes

## Thank You 🙌

We’re building Bagisto Visual to empower developers and merchants alike.
Your ideas, feedback, and contributions make the project better for everyone.

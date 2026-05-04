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

All commits must follow the [Conventional Commits](https://www.conventionalcommits.org) specification. The `commit-msg` git hook (installed automatically by Composer through CaptainHook) validates the message format locally, and a GitHub Actions workflow re-validates every commit on every pull request.

```bash
git add .
git commit -m "fix: correct section layout misalignment"
```

#### Allowed types

| Type | Use for |
| --- | --- |
| `feat` | New user-facing feature |
| `fix` | Bug fix |
| `refactor` | Code change that neither adds a feature nor fixes a bug |
| `perf` | Performance improvement |
| `style` | Formatting, whitespace, missing semicolons (no logic change) |
| `test` | Adding or correcting tests |
| `docs` | Documentation only |
| `build` | Build system or external dependencies |
| `ci` | CI configuration and workflows |
| `chore` | Other changes that do not modify source or tests |

A scope is optional and uses kebab-case (e.g. `feat(editor): ...`). Breaking changes use a `!` after the type/scope or a `BREAKING CHANGE:` footer, and trigger a major version bump on the next release.

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

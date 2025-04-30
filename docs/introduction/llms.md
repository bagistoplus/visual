# LLMs.txt

Helping AI tools like GitHub Copilot, Cursor, ChatGPT, and Claude understand **Bagisto Visual**.

## What is `llms.txt`?

Bagisto Visual provides `llms.txt` files to support language models and AI-powered tools that interact with our documentation and developer experience.

These files help large language models:

- Understand how Bagisto Visual works
- Suggest accurate code completions and section structures
- Reference correct naming conventions and architecture
- Improve search, retrieval, and autocomplete when used in IDEs or chat-based assistants

## Available Files

We provide the following routes:

- [`llms.txt`](/llms.txt): A compact overview of Bagisto Visual, including keywords and top-level concepts
- [`llms-full.txt`](/llms-full.txt): A detailed description of the framework, its architecture, usage patterns, and terminology

These files are designed for easy ingestion by AI tools and indexing systems.

## Usage with AI Tools

### GitHub Copilot / ChatGPT / Claude

These tools can use `llms.txt` files to:

- Recommend valid section structure using Blade or Livewire
- Autocomplete field definitions and settings
- Suggest markup that respects Bagisto Visual’s layout and theming conventions

---

### Cursor

If you're using Cursor, you can include `llms.txt` files in your workspace via the `@Docs` directive. This helps Cursor learn from the Bagisto Visual documentation directly for faster, more accurate suggestions.

Learn more at [cursor.sh](https://docs.cursor.com/context/@-symbols/@-docs)

---

### Other Tools

Any AI tool that supports `llms.txt` indexing or document injection can use these files to build:

- Better code generators
- Custom autocomplete providers
- Enhanced prompts for RAG pipelines

Simply include these files alongside your theme or development project.

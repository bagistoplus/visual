# LLMs.txt

Make AI coding assistants and chat interfaces understand Bagisto Visual's architecture, conventions, and patterns.

## What is `llms.txt`?

`llms.txt` is a standardized format that helps AI tools understand your framework or project. Bagisto Visual provides these files so AI assistants like GitHub Copilot, Cursor, ChatGPT, and Claude can:

- **Suggest better code** – Autocomplete section structures, settings, and Blade templates accurately
- **Follow conventions** – Use correct naming patterns, file locations, and API methods
- **Understand context** – Know about blocks, sections, templates, and how they relate
- **Provide accurate help** – Answer questions about Bagisto Visual with up-to-date information
- **Generate valid code** – Create sections and blocks that follow framework patterns

Instead of generic suggestions, AI tools can provide recommendations specific to Bagisto Visual's architecture.

## Available Files

We provide the following routes:

- [`llms.txt`](/llms.txt): A compact overview of Bagisto Visual, including keywords and top-level concepts
- [`llms-full.txt`](/llms-full.txt): A detailed description of the framework, its architecture, usage patterns, and terminology

These files are designed for easy ingestion by AI tools and indexing systems.

## Using with AI Tools

### Code Assistants (Copilot, Cursor, etc.)

AI code completion tools can reference llms.txt for context-aware suggestions:

**In Cursor:**

```
@Docs https://visual.bagistoplus.com/llms-full.txt
```

This loads Bagisto Visual documentation into Cursor's context, enabling:

- Accurate section/block scaffolding
- Correct settings field syntax
- Proper Blade directive usage
- Convention-following code generation

Learn more: [Cursor @Docs](https://docs.cursor.com/context/@-symbols/@-docs)

---

### Chat Interfaces (ChatGPT, Claude)

When asking AI chat tools about Bagisto Visual:

**Share the context:**
"I'm working with Bagisto Visual. Reference https://visual.bagistoplus.com/llms-full.txt for documentation."

The AI will then:

- Understand framework-specific terminology
- Suggest code that matches conventions
- Reference actual API methods
- Provide accurate troubleshooting

---

### Custom Integrations

Building tools that work with Bagisto Visual? Use llms.txt files for:

- **RAG Pipelines** – Embed framework knowledge in your AI system
- **Code Generators** – Generate valid sections/blocks/templates automatically
- **Documentation Chatbots** – Answer framework questions accurately
- **IDE Extensions** – Power autocomplete with framework context

Parse the files to extract structure, examples, and patterns for your use case.

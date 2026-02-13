# AGENTS.md — telegram-format-text

## Purpose

This package converts HTML to Telegram Bot API-compatible text in four output formats: `text`, `HTML`, `Markdown`, and `MarkdownV2`. It is used by WordPress plugins to format post content and notifications for Telegram delivery.

**Package**: `wpsocio/telegram-format-text`
**Namespace**: `WPSocio\TelegramFormatText`
**PHP**: 8.0+

## Architecture

```
HtmlConverter (entry point)
  └─ Environment (converter registry)
       ├─ Configuration (options store)
       └─ Converters (one per HTML tag)
            └─ BaseConverter (abstract, routes to format-specific methods)
```

**Design pattern**: Strategy pattern — each HTML tag has a dedicated converter class. The `Environment` maps tag names to converter instances. `HtmlConverter` walks the DOM tree bottom-up, delegating each node to the appropriate converter.

## Key Files

| File | Role |
|------|------|
| `src/HtmlConverter.php` | Public API. `convert($html)` and `safeTrim($html, $limitBy, $limit)` are the main entry points. |
| `src/Environment.php` | Registry of converters. `createDefaultEnvironment()` wires up all built-in converters. |
| `src/Configuration.php` | Stores options like `format_to`, `list_item_style`, `text_hyperlinks`, etc. |
| `src/Element.php` | Wraps `DOMNode` with helper methods for traversal, type checks, and attribute access. |
| `src/ElementInterface.php` | Interface contract for `Element`. |
| `src/Converter/BaseConverter.php` | Abstract base class. Routes conversion to `convertToText()`, `convertToMarkdown()`, or `convertToHtml()` based on the configured format. Contains Markdown escape character maps. |
| `src/Converter/Utils.php` | Static utilities: placeholder management, HTML entity decoding, text truncation, style parsing. |

## Converters (`src/Converter/`)

Each converter extends `BaseConverter` and handles specific HTML tags:

| Converter | Tags | Notes |
|-----------|------|-------|
| `TextConverter` | `#text` | Decodes entities, escapes Markdown special chars |
| `EmphasisConverter` | `em`, `i`, `strong`, `b` | Prevents invalid nesting of same-type emphasis |
| `LinkConverter` | `a` | Handles relative URLs, format-specific link syntax |
| `ImageConverter` | `img` | Configurable alt/title behavior via `images_in_links` option |
| `CodeConverter` | `code` | Inline and block code with language detection |
| `PreformattedConverter` | `pre` | Preformatted text blocks |
| `ListBlockConverter` | `ol`, `ul` | List containers |
| `ListItemConverter` | `li` | Nested list support with configurable bullet styles |
| `BlockquoteConverter` | `blockquote` | Only formatted in MarkdownV2 |
| `TableConverter` | `table`, `tr`, `th`, `td`, `thead`, `tbody`, `tfoot`, `colgroup`, `col`, `caption` | Custom cell/row separators |
| `HorizontalRuleConverter` | `hr` | Horizontal rules |
| `SpoilerConverter` | `tg-spoiler`, `span` | Telegram spoilers (MarkdownV2 only) |
| `CommentConverter` | `#comment` | Strips HTML comments |
| `DefaultConverter` | fallback | Passes through children's text for unhandled tags |

## Conversion Flow

1. **Prepare HTML** (`prepareHtml`): Normalize whitespace, strip `<head>/<script>/<style>`, convert `<br>` to newlines.
2. **Parse DOM**: Load into `DOMDocument` with charset detection.
3. **Recursive traversal** (`convertChildren`): Process children first (bottom-up), then convert each node via its registered converter.
4. **Format dispatch**: `BaseConverter::convert()` calls the format-specific method (`convertToText`, `convertToMarkdown`, or `convertToHtml`).
5. **Cleanup** (`cleanUp`): Replace placeholders, collapse blank lines, trim.

## Configuration Options

Key options passed to `HtmlConverter` constructor or `setOptions()`:

- `format_to` — Output format: `text` (default), `HTML`, `Markdown`, `MarkdownV2`
- `list_item_style` / `sub_list_item_style` — Bullet characters (`-`, `◦`)
- `text_hyperlinks` — `retain` (show URLs in text mode) or `remove`
- `relative_links` — `clean` (strip) or `retain`
- `elements_to_remove` — Tags to strip entirely (default: `['figcaption', 'form']`)
- `remove_display_none` — Strip elements with `display:none` style
- `should_convert_cb` — Custom callback to filter elements
- `table_cell_sep` / `table_row_sep` — Table formatting separators
- `images_in_links` — How to handle images inside links

## Testing

- **Framework**: Pest (PHPUnit-based)
- **Strategy**: Snapshot testing — 13 HTML input files tested against 4 output formats (52 snapshots), plus unit tests
- **Run tests**: `vendor/bin/pest` from this directory
- **Regenerate snapshots**: `php bin/snapshots.php`
- **Test data**: `tests/data/input/` (HTML files) and `tests/data/output/` (expected outputs per format)

**Input files**:

| File | Description |
|------|-------------|
| `custom-text-1.html` | Basic formatting: bold, italic, links, spoilers |
| `custom-text-2.html` | Various formatting combinations |
| `custom-text-3.html` | More complex formatting examples |
| `custom-text-4.html` | Additional formatting cases |
| `custom-text-5.html` | Further formatting combinations |
| `custom-text-6.html` | Additional scenarios |
| `custom-text-7.html` | More formatting examples |
| `custom-text-8.html` | Figures with `<figcaption>` (verifies caption stripping) |
| `malformed-1.html` | Malformed/broken HTML handling |
| `wc-invoice-1.html` | WooCommerce invoice (complex real-world HTML) |
| `wc-new-order-1.html` | WooCommerce order notification |
| `wp-dev-docs-1.html` | WordPress documentation page |
| `wp-post-1.html` | WordPress post content |

**Output naming convention**: `{input-name}-{format}.txt` (e.g., `custom-text-1-markdown.txt`)

## Adding a New Converter

1. Create a class in `src/Converter/` extending `BaseConverter`.
2. Implement `getSupportedTags(): array` returning the tag names.
3. Override `convertToText()`, `convertToMarkdown()`, and/or `convertToHtml()`.
4. Register it in `Environment::createDefaultEnvironment()`.
5. Add test HTML to `tests/data/input/` and regenerate snapshots.

## Common Pitfalls

- **Markdown escaping**: `MarkdownV2` requires escaping many special characters. `TextConverter` and `BaseConverter` handle this — check `HTML_TO_MARKDOWN_V2_MAP` and `MARKDOWN_V2_ESCAPE_CHARS` constants.
- **Bottom-up traversal**: Children are converted before parents. A converter receives its children's already-converted text via `$element->getChildrenAsString()`.
- **Placeholder system**: Spaces and tabs inside `<pre>`/`<code>` blocks use placeholders (`{:space:}`, `{:tab:}`) to survive the cleanup phase. See `Utils::processPlaceholders()`.
- **Block vs inline**: `Element::isBlock()` determines newline insertion. The block element list is hardcoded in `Element`.

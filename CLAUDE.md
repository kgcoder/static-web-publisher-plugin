# Reader's Web Publisher — CLAUDE.md

## Project Overview

**Reader's Web Publisher** is a WordPress plugin (plugin slug: `static-web-publisher`, PHP prefix: `stwbpb_`) that makes WordPress sites part of the **Reader's Web** — a new browsable web ecosystem where site owners provide content but readers control styling and layout.

The plugin serves content in several document formats (HDOC, CDOC, CONDOC and their embedded variants), embeds metadata into regular HTML pages for compatible clients, and can also serve the full Reader UI directly from WordPress.

The **RW Reader** Chrome extension is the primary client for these formats. When the user mentions "extension," they mean this extension.

## Detailed Docs

Read the relevant file before working in that area:

| File | Covers |
|------|--------|
| [noinclude/dev-docs/readers-web.md](noinclude/dev-docs/readers-web.md) | The Reader's Web, document formats, embedded variants, visible connections (flinks), list of spec files |
| [noinclude/dev-docs/architecture.md](noinclude/dev-docs/architecture.md) | Entry point, `includes/` files, reader template, `reader/` contents, adapter, content filters |
| [noinclude/dev-docs/display-modes.md](noinclude/dev-docs/display-modes.md) | The five display modes and how CDOC/CONDOC resolve them |
| [noinclude/dev-docs/endpoints.md](noinclude/dev-docs/endpoints.md) | Rewrite rules / custom endpoints, static document files |
| [noinclude/dev-docs/settings-and-meta.md](noinclude/dev-docs/settings-and-meta.md) | Post meta keys, `stwbpb_settings` option, republishing policy and RWRL draft |
| [noinclude/specs/](noinclude/specs/) | Format specifications |

## Key Rules

- [reader/](reader/) is copied from the extension and must stay **literally identical** to the extension's copy. It never talks to the host directly; it calls `g.hostAdapter`, implemented in [adapter/HostAdapter.js](adapter/HostAdapter.js). Anything genuinely different per project belongs in `adapter/`, not `reader/`.
- All functions and options use the `stwbpb_` prefix.
- Do not use optional chaining or nullish coalescing.
- After adding or changing rewrite rules, go to **Settings > Permalinks** and click **Save Changes**.
- In production (`WP_DEBUG` false) the reader JS is served from a minified bundle. To build it (esbuild is installed globally):
esbuild adapter/startup.js --bundle --minify --format=esm --target=es2019 --outfile=dist/reader.bundle.min.js
- Do not attempt to test changes in the browser yourself (e.g. via wp-cli, Playwright, or logging into wp-admin) — it doesn't work in this environment. After implementing and verifying with static checks (php -l, node --check, build steps), let the user test the result themselves.

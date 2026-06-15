# License Notice

This specification is part of the Default Web project and is licensed under 
**Creative Commons Attribution-NoDerivatives 4.0 International (CC BY-ND 4.0)**.

You are free to share, copy, and redistribute this specification in any medium or format, 
provided you give appropriate credit, provide a link to the license, and do **not** 
modify the content. 

For details, see: https://creativecommons.org/licenses/by-nd/4.0/

---

# HDOC Specification (Draft)

**Status:** Early draft — subject to change
**Document Type:** HDOC (Hypertext Document)

HDOC is a static, script-free hypertext document format designed for the Default Web.
It provides predictable rendering, visible connections, and long-term stability.
This specification defines the structure, required/optional components, and parsing rules for HDOC documents.

---

# 1. Overview

HDOC is an XML-based document format that may contain an embedded HTML fragment.
The key goals of the format are:

* **Static** — No JavaScript; no inline or custom CSS.
* **Standardized** — All clients render HDOCs consistently.
* **Interoperable** — Visible connections, comments, panels, and metadata behave identically across clients.
* **Self-contained** — No dependencies except images and iframes referenced inside content.

An HDOC represents one hypertext document.
It may optionally reference:

* a paired interactive HTML page,
* a JSON comments document,
* parent/origin documents (when the HDOC is a copy),
* media remappings (when copied),
* outgoing connections (via the shared *Connections* format).

---

# 2. Root Structure

The root element of every HDOC is:

```xml
<hdoc> … </hdoc>
```

Children (in this order):

1. `<metadata>` (optional)
2. `<header>` (optional)
3. `<fallback>` (optional)
4. `<content>` (required)
5. `<panels>` (optional)
6. `<copy-info>` (optional; required only for copies)
7. `<connections>` (optional; defined in a separate specification)

Any additional elements are **invalid**.

---

# 3. Metadata

The metadata section functions similarly to the `<head>` section in HTML.

```xml
<metadata>
    <title>Visible title here</title>
    <!-- more metadata types may be added later -->
</metadata>
```

## 3.1 `<title>` (optional, but strongly recommended)

Contains the document’s title in plain text.
Client software may display this in browser UI, tabs, link previews, etc.

Other metadata fields are not yet defined.

---

# 4. Header

The `<header>` section contains **visible metadata** intended for display, such as title, author, and date.
It is optional, but recommended for human-readable documents.

```xml
<header>
    <h1>Visible Title</h1>
    <author>Author Name</author>
    <date>2025-10-15</date>
</header>
```

Rules:

* `<h1>` content **must be plain text only**. Any HTML inside must be stripped.
* Clients may choose which header fields to show.
* Additional header fields may be added in future versions.

If the header is omitted, the title *may* appear inside `<content>`, but this is discouraged because updates may break floating links.

---

# 5. Fallback

The `<fallback>` section is intended to contain HTML content shown to users who do not have compatible software to properly view the document.

Typically, this section includes a message explaining that special software (such as a browser extension or dedicated client) is required to view the document correctly, often with one or more hyperlinks. For this reason, raw HTML is allowed inside the `<fallback>` tags.

During parsing by a compliant client, the `<fallback>` element MUST be removed entirely before the document is processed as XML.

---

# 6. Content

The `<content>` element contains the **HTML** or plain text content of the document.

```xml
<content><p>This is a paragraph.</p></content>
```

### Requirements:

* HTML begins **immediately after the opening tag**, with no indentation or whitespace before the first element (recommended for compatibility).
* HTML must not contain:

  * `<script>` tags
  * `<style>` tags
  * inline styles
  * custom CSS classes
* Only predefined CSS classes (from a global default list) may be used.

All HTML appears **only** inside `<content>`.
The rest of the HDOC is XML.

Parsing note:
Clients must extract the HTML block from `<content>` using a **regular expression** before parsing the XML (details below).

* `<content>` may include `type="text"`.
* When `type="text"`, the content must be treated as *literal plain text*.
* Before rendering, the content must be **escaped**, so any HTML inside is shown as source code, not interpreted.

---

# 7. Panels

The `<panels>` section defines standardized UI panels that appear in all clients.
It may contain three types of panels: `<top>`, `<side>`, and `<bottom>`.

```xml
<panels>
    <top>…</top>
    <side>…</side>
    <bottom>…</bottom>
</panels>
```

All panels are **optional**.

---

## 7.1 `<top>` Panel

Contains branding and navigation links.

Allowed child elements:

### 7.1.1 `<site-name>`

```xml
<site-name href="https://example.com">My Site</site-name>
```

* Either `<site-name>` **or** `<logo>` may be used (not both).

### 7.1.2 `<logo>`

```xml
<logo src="https://example.com/logo.png" href="https://example.com"/>
```

Attributes:

* `src` (required) — image URL
* `href` (optional)

### 7.1.3 `<a>`

```xml
<a href="https://example.com/about">About</a>
```

Multiple allowed.

---

## 7.2 `<side>` Panel

Contains comments or an interactive page.

```xml
<side side="left"> … </side>
```

Attributes:

* `side="left"` or `"right"` (default: `"right"`)

### Child elements:

#### 7.2.1 `<comments>`

```xml
<comments title="Comments" empty="No comments yet">https://…/comments.json</comments>
```

Attributes:

* `title` (optional)
* `empty` (optional)

Content:

* URL of a static-comments JSON array

#### 7.2.2 `<ipage>`

URL of an interactive HTML page displayed in the side panel.

```xml
<ipage>https://…/interactive</ipage>
```

---

## 7.3 `<bottom>` Panel

```xml
<bottom>
    <section title="About">
        <a href="https://example.com/me">About Me</a>
    </section>
    <bottom-message>All rights reserved 2025</bottom-message>
</bottom>
```

Child elements:

### 7.3.1 `<section>`

Attributes:

* `title` (optional)

Contains multiple `<a>` elements.

### 7.3.2 `<bottom-message>`

Contains plain text.

---

# 8. Copy Info

`<copy-info>` is used only when the HDOC is a copy of another HDOC.
In that case, this section becomes **required**.

```xml
<copy-info>
    <source copied-at="2025-01-01T12:00:00Z">https://example.com/original</source>
    <media-mappings>…</media-mappings>
</copy-info>
```

---

## 8.1 `<source>` (one or more)

Attributes:

* `copied-at` (required): ISO-8601 timestamp

If copying a copy, include multiple `<source>` entries to preserve the history.

---

## 8.2 `<media-mappings>` (optional)

Contains remapping rules for resource URLs.

```xml
<media-mappings>
    <m>
        <old>https://example.com/img.jpg</old>
        <new>https://copy.com/img.jpg</new>
    </m>
</media-mappings>
```

---

# 9. Connections

Connections are defined using the shared *Connections* format.
They may appear in HDOC, CDOC, CONDOC documents.

```xml
<connections> … </connections>
```

Specification: `specs/connections.md` (not included here)

---

# 10. Parsing Rules for HDOC Documents

An HDOC document contains a mixture of:
* XML — used for all structural elements
* HTML — allowed only inside the `<content>` and `<fallback>` elements

To prevent embedded HTML from interfering with XML parsing, compliant clients MUST process HDOC documents using the following steps, in order:

### Step 1: Remove `<fallback>` element (if present)

The `<fallback>` element exists solely to provide alternative content for environments that do not support the HDOC format. It commonly contains raw HTML that may break XML parsing.

Before any XML parsing occurs, the `<fallback>` element and all of its contents MUST be removed entirely.

### Step 2: Strip HTML from `<h1>`

If the `<h1>` element contains HTML markup, all tags MUST be removed, and only the resulting plain text content MUST be preserved.

### Step 3: Extract the HTML block from `<content>`

The `<content>` element typically contains raw HTML. This HTML MUST be extracted as a string (for example, using a regular expression or equivalent mechanism) and stored separately before XML parsing.

### Step 4: Parse the remaining document as XML

After the `<fallback>` element has been removed and the `<content>` HTML has been extracted, the remaining document MUST be parsed as XML.

### Step 5: Sanitize the HTML found in `<content>`

The HTML extracted from `<content>` MUST be sanitized according to the client’s security model before rendering.

---

# 11. Future Additions

Future versions of HDOC may add:

* `<footer>` element
* Additional metadata fields
* More structured header fields
* Standardized themes and CSS class lists
* More panel types
* More copy-tracking capabilities

Everything in this document is subject to change during the draft phase.
# License Notice

This specification is part of the Default Web project and is licensed under 
**Creative Commons Attribution-NoDerivatives 4.0 International (CC BY-ND 4.0)**.

You are free to share, copy, and redistribute this specification in any medium or format, 
provided you give appropriate credit, provide a link to the license, and do **not** 
modify the content. 

For details, see: https://creativecommons.org/licenses/by-nd/4.0/

---

# Embedded HDOC Specification (Draft)

**Status:** Early draft — subject to change
**Document Type:** Embedded HDOC (Embedded Hypertext Document)


An **Embedded HDOC** allows an HDOC to coexist inside a regular HTML page.
The goal is to serve **both the original HTML page and an HDOC from the same URL**, making content available to both ordinary visitors and HDOC-aware clients.

---

## 1. Overview

* **Purpose:** Embed HDOC content inside a standard HTML page without requiring a separate URL.
* **Behavior:** Ordinary visitors see the HTML page. HDOC-aware software constructs an HDOC from the HTML and JSON data.
* **Use Case:** Works with existing websites, e.g., WordPress pages, without duplicating content.

---

## 2. Embedding Rules

### 2.1 Marking the HTML Content

The element containing the main visible content of the page **must** have the class:

```html
class="hdoc-content"
```

This signals to client apps which element to extract.

---

### 2.2 HDOC JSON Data

A `<script>` tag with `type="application/json"` and `id="hdoc-data"` contains all additional information needed to construct the HDOC.

Example:

```html
<script type="application/json" id="hdoc-data">
{
  "removal-selectors": ".some-class,.other-class",
  "header": {
    "h1": "The Title",
    "author": "John Doe",
    "date": "October 13, 2025"
  },
  "panels": { ... },
  "connections": [ ... ]
}
</script>
```

#### Fields:

* **removal-selectors** (optional): CSS selectors to remove unwanted elements from the content.
* **header** (optional):

  * `h1`: Page title
  * `author`: Author name
  * `date`: Publication date
* **panels** (optional): Defines top, side, and bottom panels for standardized UI (see HDOC panels spec).
* **connections** (optional): Array of connection objects (see specification at `specs/connections.md`).

---

### 2.3 Panels JSON Structure

Example:

```json
"panels": {
  "top": {
    "site-name": "My website",
    "home-url": "https://example.com",
    "site-logo": "https://example.com/icon.png",
    "links": [
      { "href": "https://example.com/archive", "text": "Archive" }
    ]
  },
  "side": {
    "side": "left",
    "ipage": "https://example.com/interactive-page",
    "comments": {
      "url": "http://example.com/json-comments/?post=19",
      "title": "Comments",
      "empty": "No comments yet"
    }
  },
  "bottom": {
    "sections": [
      {
        "title": "Section 1",
        "links": [{ "href": "https://example.com/about", "text": "About us" }]
      },
      {
        "title": "Section 2",
        "links": [{ "href": "https://example.com/contacts", "text": "Contacts" }]
      }
    ],
    "bottom-message": "This is a bottom message"
  }
}
```

---

### 2.4 Connections JSON Structure

Embedded HDOC connections follow the same structure as HDOC floating links:

```json
"connections": [
  {
    "url": "https://example.com/dates",
    "title": "Dates",
    "hash": "d79712",
    "flinks": [
      "i:769;l:256;h:ff3d6e;e:Vy4=_i:0;l:8;h:e68ee0;e:RXM=",
      "i:1278;l:16;h:512358;e:THM=_i:35;l:11;h:1e5fac;e:Y3M="
    ]
  },
  {
    "url": "https://example.com/collage",
    "title": "Collage",
    "hash": "54dfa4",
    "flinks": [
      "i:2029;l:97;h:72bcf5;e:RS4=_p|x:79.772;y:142.467;r:0.147",
      "i:2423;l:79;h:7f7a20;e:Qi4=_p|x:81.226;y:142.1;r:0.147"
    ]
  }
]
```

---

## 3. Notes

* Clients must extract content from `.hdoc-content` and parse the JSON to build an HDOC.
* `document.title` is used automatically if `header.h1` is not provided.
* This format is **still a draft**; fields may be added in the future.
* The Embedded HDOC format allows a website to **maintain a single URL** while making content available to both HTML and HDOC-aware software.
> ⚠️ **DRAFT — NOT FINAL — NOT LEGAL ADVICE — DO NOT PUBLISH AS-IS**
>
> This is a working draft produced during design discussion, not a reviewed or
> adopted license. It has **not** been checked by a lawyer. Wording,
> definitions, and especially the bracketed `[ ... ]` placeholders below are
> open decisions, not settled terms. Do not treat this file, or any URL it
> might end up published at, as a live, enforceable license until:
>
> 1. It has been reviewed by qualified IP counsel, and
> 2. The open questions in the "Notes for review" section at the bottom have
>    been resolved.
>
> If this file is committed to a public repo, that visibility is intentional
> for collaboration purposes — it is still a draft, not a publication of the
> license.

---

# Reader's Web Republishing License (RWRL) — version 1.0 (DRAFT)

**Canonical URL:** `[TBD — to be published at a stable URL on the Reader's Web
site once finalized; this repo copy should then defer to that URL as the
authoritative text]`

## 0. Purpose

The Reader's Web is a browsable ecosystem of documents (HDOC, CDOC, CONDOC,
and their embedded variants) connected by **visible connections**. Because a
visible connection can be broken if the document it targets is edited, moved,
or deleted, the format defines a **republishing** mechanism: copying an
existing document and hosting it at a new URL, so connections can point to a
stable copy instead of an original that may change out from under them.

This license exists so that:

- Authors publishing in these formats do not need to fear that enabling this
  mechanism means giving up their rights, and
- Republishers (individual sites, or aggregation services) have clear,
  predictable terms instead of having to guess or negotiate case by case.

This license governs **only** the republishing mechanism defined by the
Reader's Web document formats. It does not apply to, and grants no rights
over, ordinary HTML pages or other content that was not published using
these formats. See §6.

## 1. Definitions

- **"Document"** — an HDOC, CDOC, or CONDOC file, or the embedded-variant
  equivalent, published by a Rights Holder using Reader's Web-compatible
  software.
- **"Rights Holder"** — the copyright owner of a Document's content (typically
  the site operator who published it).
- **"Republisher"** — any person or service that copies a Document and hosts
  it at a new URL under one of the licenses below.
- **"Copy"** — a republished instance of a Document, carrying a valid
  `<copy-info>` block per the HDOC/CDOC specification.
- **"Republishing Policy"** — the `<republishing-policy>` metadata value (or
  equivalent plugin setting) a Rights Holder attaches to a Document, which
  determines which tier of this license, if any, applies.

## 2. How this license attaches

A Document's Republishing Policy determines the license grant:

| Policy value | Meaning |
|---|---|
| `do-not-republish` / `prohibit` | **No license granted.** See §3. |
| *(tag absent)* / `implicit_allow` (default) | **Stabilization License** granted. See §4. |
| `allow` / `explicit_allow` | **Aggregation License** granted (superset of §4). See §5. |

By publishing a Document using Reader's Web-compatible software without
changing the default Republishing Policy, the Rights Holder grants the
Stabilization License (§4). This default, and what it means, is disclosed in
the format specification and in the publishing software's settings — it is
not a silent or hidden opt-in. A Rights Holder who prefers not to grant any
license simply sets the policy to `prohibit`.

**This mechanism only applies to Documents actually published in Reader's
Web formats by their Rights Holder.** It has no bearing on content that
exists only as a regular HTML page and was never published this way — see
§6.

## 3. Prohibited

If the Republishing Policy is `do-not-republish` / `prohibit`, no license is
granted under this document. Republishing requires separate, direct
permission from the Rights Holder.

## 4. Stabilization License (default tier)

Subject to the conditions below, the Rights Holder grants a worldwide,
royalty-free, non-exclusive, **revocable** license to:

- Reproduce the Document and host a Copy at a new URL,
- **solely** for the purpose of providing a stable target so that visible
  connections pointing at the Document continue to resolve if the original
  becomes unavailable, is moved, or is materially altered.

**Conditions:**

a. The Copy must carry a valid `<copy-info>` block identifying the original
   URL, per the HDOC/CDOC specification.
b. The content and title must not be materially altered, beyond the format
   transformations the specification permits (e.g. `<media-mappings>`
   rewriting of asset URLs).
c. The Copy must be clearly presented as a copy, not as the original.
d. This grant does **not** authorize distributing the Copy as part of a
   feed, index, aggregator, or collection of documents gathered from
   multiple sources for browsing/discovery purposes — that use case requires
   the Aggregation License (§5).

## 5. Aggregation License (opt-in tier)

Subject to the conditions below, a Rights Holder who sets their Republishing
Policy to `allow` / `explicit_allow` grants everything in §4, **plus** a
worldwide, royalty-free, non-exclusive, **revocable** license to:

- Include the Document (as a Copy) in a feed, index, aggregation service, or
  similar collection assembled from multiple sources, for the purpose of
  discovery, browsing, or archival access.

**Conditions (in addition to §4's, except (d) which no longer applies):**

a. Content and title must be preserved.
b. Panel/sidebar information may be preserved or omitted, but if omitted or
   altered it must not be **replaced with anything that misrepresents the
   Copy's source or affiliation**, or otherwise misleads the reader about
   where the content came from.
c. A visible, reasonably prominent link back to the original URL is
   **mandatory** — this is a non-negotiable condition of the grant, not
   optional attribution.
d. The Copy must not be presented in a way that implies endorsement by, or
   affiliation with, the Rights Holder beyond the attribution required by
   `<copy-info>`.
e. `[OPEN — see Notes for review: should commercial redistribution require a
   separate/further tier, or additional conditions, beyond what's listed
   here?]`

## 6. Explicitly out of scope

This license does **not** apply to, and grants no rights regarding:

- Ordinary HTML pages or other web content that was not published as a
  Document under a Reader's Web format by its own Rights Holder — regardless
  of whether such content could technically be parsed or converted into an
  HDOC/CDOC. No license is implied by the mere technical possibility of
  conversion, and no license is granted by a site's silence or lack of an
  opt-out signal if that site never adopted these formats in the first
  place. Republishing such content requires separate permission from its
  owner, obtained outside this license.

## 7. Revocation / takedown

A Rights Holder may revoke the license granted for a specific Document (or
all Documents) at any time by notifying the Republisher `[OPEN — via what
channel? e.g. a defined contact mechanism, or direct contact if none
exists]`. Upon receiving such notice, the Republisher must cease
distributing the affected Copy or Copies within `[OPEN — time period, e.g.
14 days]`. Continued distribution after that period is outside this
license.

Revocation does not require cause and is not an admission that the prior
distribution was unlicensed — it simply ends the license going forward.

## 8. No transfer of rights; reservation

This license does not transfer copyright or any other right in the
Document. The Rights Holder retains all rights not expressly granted here,
including the right to revoke as described in §7. This license does not
grant any trademark rights, and does not address moral rights in
jurisdictions that recognize them separately from copyright — `[OPEN: may
need jurisdiction-specific language here]`.

## 9. No warranty

The Document is provided "as is." The Rights Holder makes no warranties
regarding the Document and disclaims liability for a Republisher's use of
it, to the maximum extent permitted by law.

## 10. Versioning

This is version 1.0 (draft) of the Reader's Web Republishing License. Future
versions may be published under new version numbers; a Document's
Republishing Policy is interpreted under the license version current at the
time the Document was published, unless the specification says otherwise.

---

## Notes for review (remove before publishing)

Open questions raised during drafting that still need a decision:

1. **Commercial vs. non-commercial aggregation** (§5e) — should monetized
   redistribution (ads, paywalls, etc. against verbatim content) require
   stricter conditions, or a further explicit tier, beyond attribution +
   link-back? This was flagged as the main friction point in real-world
   aggregator disputes (news snippet-tax cases, Meltwater/AP, etc.) even
   when attribution was present.
2. **Takedown notice channel and timeframe** (§7) — needs a concrete
   mechanism (email? a metadata field? a form?) and a concrete number of
   days.
3. **Canonical publication URL** — where this license will actually live
   once finalized (site vs. spec repo vs. both, with the site as source of
   truth).
4. **Per-aggregator allowlisting** — deliberately left **out** of this
   license by design decision (see project discussion): granting a specific
   named aggregator narrower/broader rights than the public tiers is a
   bilateral arrangement outside this license, not a metadata-driven
   allowlist. Revisit only if real demand emerges.
5. **HTML-source conversion** — deliberately excluded entirely (§6), by
   design decision. Do not expand scope to cover it without a separate
   opt-in mechanism on the HTML side (e.g. a meta tag), which does not yet
   exist.
6. **Legal review** — this entire draft needs a qualified IP lawyer's pass
   before anything resembling this text is published as a live license,
   especially §7 (revocation mechanics) and §5 (commercial use) and §8
   (moral rights / jurisdiction).

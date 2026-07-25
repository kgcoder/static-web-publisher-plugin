# iPad freeze on connected-document download — investigation log

## Symptom

On an old iPad (iOS 12.5.7), downloading a single connected document (clicking a flink to
load it into the right panel) causes the whole reader UI to freeze for a very long time
(~47 seconds observed). During the freeze:

- The page's own spinner (`g.pdm.showMainDocSpinner()`, a CSS animation) keeps rotating.
- The page is still scrollable (touch scrolling works).
- Top-bar buttons are unresponsive — clicks/taps do nothing.
- After the freeze ends, the downloaded document appears and everything works normally.

This has only been reproduced on the old iPad. It was not reproduced (or was much faster)
when testing "on the real site" from a normal/modern device.

Only ever tested with **one** connected document downloaded per session (fresh page load →
download one page) — not a cumulative "download all" scenario.

## Where the delay was first localized

The delay was traced to `applyFlinksOnTheRight()` in
[reader/ReadingManager.js](../reader/ReadingManager.js) (search for that function name — line
numbers have shifted during debugging). It schedules a `requestAnimationFrame(cb)` and the
gap between scheduling and the callback actually firing was ~47s, even though every
individual piece of work *inside* the callback (`checkIfFlinksAreBroken`,
`fixRightFlinksAutomaticallyIfNeeded`, `prepareRightLinks`, `addFlinksToRightDiv`,
`redrawFlinks`) measured fast once it did fire.

Call chain for a single-document download: a flink click → `downloadOnePage()` in
ReadingManager.js → `loadStaticContentFromUrl()` (fetch/parse) → `addNewRightDocDivs()` →
`addOneRightDivForHDoc()` → `populateDivWithTextFromDoc()` in
[reader/NoteDivsMethods.js](../reader/NoteDivsMethods.js) (inserts the downloaded document's
HTML into the live DOM) → `showTab()` → `redrawFlinks()` → `applyFlinksOnTheRight()` (the
~47s wait happens here).

## Things ruled out (with evidence)

1. **`alert()` calls corrupting the measurement.**
   Initially suspected, because `alert()` is blocking and pauses timers/rAF on iOS while a
   dialog is open, and there was an alert sitting between "rAF scheduled" and "rAF fires."
   **Ruled out**: the user confirmed the freeze pre-dated any debugging/alerts being added at
   all — it's a real freeze, not an artifact of dismiss-time on a dialog.

2. **Layout/style recalculation of the newly-inserted document content.**
   Theory: `notePresentationDiv.innerHTML = html` (NoteDivsMethods.js) queues a big DOM
   subtree whose style/layout cost is deferred by the browser until right before it paints
   the next frame — i.e. exactly the rAF gap.
   **Test**: added timestamps around the `innerHTML` assignment, then forced a synchronous
   layout immediately after via `notePresentationDiv.offsetHeight` (forces style+layout on
   the spot instead of letting the browser defer it).
   **Result**: forced layout took only ~1.17s. Ruled out — nowhere near 47s.

3. **The per-image forced-layout-thrashing loop** in `populateDivWithTextFromDoc`
   (NoteDivsMethods.js — the loop that read `image.width` then wrote `image.style.width` per
   `<img>`, interleaving layout reads and writes).
   **Test**: commented out the whole loop.
   **Result**: no change in freeze duration. Ruled out.

4. **Image decode/paint cost in general** (images are lazy — "only start loading when I
   scroll to them" — but maybe something was forcing early decode).
   **Test**: stripped all `<img>` tags out of the HTML entirely before insertion (regex strip
   right before the `innerHTML` assignment), so zero images are even present in the DOM.
   **Result**: no change in freeze duration. Ruled out.

5. **Screen dimming / auto-lock suspending the page.**
   **Test**: asked the user to watch the iPad directly.
   **Result**: screen does not dim or lock during the wait. Ruled out.

6. **Page/tab visibility changes** (`document.hidden`, `blur`/`focus`, bfcache
   freeze/resume) suspending timers.
   **Test**: added a non-blocking `console.log`-based listener in
   [reader/Globals.js](../reader/Globals.js) for `visibilitychange`, `pagehide`, `pageshow`,
   `freeze`, `resume`, `blur`, `focus`, each logging `performance.now()`.
   **Result**: only one log fires, at initial page load (`pageshow` at page open). Nothing
   fires during or around the 47s freeze. Ruled out — the page is never marked hidden/blurred.

7. **A slow/stalled network request** (e.g. the `/sw-proxy/` call, or image requests) blocking
   something the UI depends on.
   **Test**: checked the Network panel in Safari Web Inspector during the freeze.
   **Result**: `sw-proxy` finishes quickly; the batch of image requests all finish quickly
   too. Ruled out.

8. **A JS timer (`setTimeout`/`setInterval`) with a long delay** somewhere in the flow.
   **Test**: grepped the entire `reader/` codebase for every `setTimeout`/`setInterval` call
   and checked every delay value.
   **Result**: nothing close to 47s anywhere (longest is 2000ms, a toast-message timeout in
   helpers.js; most are 0/10/100/150/500ms). Ruled out.

## Key observation from Web Inspector Timelines

A Web Inspector timeline recording was captured spanning the freeze (a ~27MB JSON export).
During the entire 47-second window:

- The **JavaScript** track shows no activity.
- The **Layout & Rendering** track shows no activity.
- The **Network** track shows nothing pending (everything already finished before the freeze
  visually resolves).

Yet the interface is unresponsive to touch (buttons don't respond) while the CSS spinner
animation and touch-scrolling keep working.

### Working hypothesis (unconfirmed)

CSS-driven compositor animations and iOS's native touch-scrolling are known to run on a
different thread/process than the page's own JS (WebContent process on iOS). A pause that is
invisible to Safari's own per-page Web Inspector timeline, but that stalls the WebContent
process itself (e.g. an OS-level memory-pressure pause, common on RAM-constrained old
hardware), would explain every symptom simultaneously:
- No JS/Layout/Network activity recorded (nothing is running to record).
- Spinner and scrolling keep working (different process/thread).
- Buttons unresponsive (dispatching a click needs the paused WebContent process).
- IPad-only, old-hardware-only reproduction.

This has **not been confirmed**. It's the leading theory only because every other avenue
(CPU-bound work, network, timers, visibility/backgrounding, images) has been actively ruled
out with a targeted test, and this is the one class of explanation that isn't visible to
Safari Web Inspector's page-level profiler at all.

Note: a candidate contributor to memory pressure — the newly downloaded document's own
images — was already ruled out in test #4 above (freeze unchanged with zero images present).
If memory pressure is still the right track, the source of that pressure is something other
than this specific document's images (e.g. general reader/document memory retention, or
something unrelated to this flow entirely).

## Suggested next steps (not yet tried)

1. **Xcode Instruments** (requires a Mac + Xcode + the iPad connected). Unlike Safari Web
   Inspector, Instruments' Time Profiler / VM Tracker / Activity Monitor can see OS-level
   process suspension and memory-pressure events that are invisible to the page-level
   JS/Layout timeline. This is the most direct way to confirm or rule out the memory-pressure
   hypothesis above.
2. Check the **Memory** timeline/graph in Web Inspector (if available in this Safari version)
   for a spike coinciding with the freeze, even though images were ruled out as the trigger.
3. Try the same single-document-download test on a different old-but-slightly-newer iOS
   device (more RAM) to see if the freeze duration scales with available memory — would
   support or weaken the memory-pressure theory.
4. Consider whether `rightNotesData` or other reader state accumulates unreleased references
   across the session that could contribute to memory pressure even on a "fresh" single
   download (e.g. anything retained from the *left/main* document, embedded doc data, or
   collage/canvas buffers allocated earlier in the page's life).

## Current state of debug instrumentation

All debug instrumentation has been removed. `reader/Globals.js` and
`reader/NoteDivsMethods.js` are back to matching `HEAD` exactly — the visibility/lifecycle
logger, the `<img>`-stripping regex, and the commented-out per-image loop are all gone;
images and their width/style/onload handling behave normally again.


No other files have uncommitted changes related to this investigation. Anyone picking this
back up starts from a clean slate

## Environment notes

- Reproduced only on: iPad (old model), iOS 12.5.7.
- Not clearly reproduced (or much less severe) on modern devices / "the real site" testing.
- `WP_DEBUG` must be `true` to test raw `reader/*.js` ES module edits directly; if testing
  against the production bundle, rebuild first with:
  `esbuild reader/readerStartUp.js --bundle --minify --format=esm --target=es2019 --outfile=dist/reader.bundle.min.js`
  (confirmed this was being done correctly — the custom `Globals.js` console.log did show up
  in `reader.bundle.min.js` during testing).

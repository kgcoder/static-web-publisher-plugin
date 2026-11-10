(function () {
    // The store URL lives only here: the link element is created when the
    // popup opens and removed when it closes, so it never sits in the page markup.
    var STORE_URL = 'https://chromewebstore.google.com/detail/rw-reader/hlckcdbgknflkkciojgdbhomdnegimbm';

    var overlay = null;

    function closePopup() {
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
        overlay = null;
        document.removeEventListener('keydown', onKeyDown);
    }

    function onKeyDown(e) {
        if (e.key === 'Escape' || e.key === 'Esc') closePopup();
    }

    function openPopup(text) {
        closePopup();

        overlay = document.createElement('div');
        overlay.className = 'stwbpb-rw-badge-overlay';
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closePopup();
        });

        var popup = document.createElement('div');
        popup.className = 'stwbpb-rw-badge-popup';
        popup.setAttribute('role', 'dialog');
        popup.setAttribute('aria-modal', 'true');

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'stwbpb-rw-badge-popup-close';
        closeBtn.setAttribute('aria-label', 'Close');
        closeBtn.textContent = '✕';
        closeBtn.addEventListener('click', closePopup);

        var message = document.createElement('p');
        message.className = 'stwbpb-rw-badge-popup-text';
        message.textContent = text;

        var link = document.createElement('a');
        link.className = 'stwbpb-rw-badge-popup-link';
        link.href = STORE_URL;
        link.target = '_blank';
        // nofollow: search engines must not treat this badge link (shown on every
        // site using the plugin) as an endorsement / widget link scheme.
        link.rel = 'noopener nofollow';
        link.textContent = 'Get RW Reader for Chrome';

        popup.appendChild(closeBtn);
        popup.appendChild(message);
        popup.appendChild(link);
        overlay.appendChild(popup);
        document.body.appendChild(overlay);
        document.addEventListener('keydown', onKeyDown);
        link.focus();
    }

    document.addEventListener('click', function (e) {
        var target = e.target;
        var badge = target && target.closest ? target.closest('.stwbpb-rw-badge-link') : null;
        if (!badge) return;
        e.preventDefault();
        openPopup(badge.getAttribute('data-popup-text') || '');
    });
})();

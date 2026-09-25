(() => {
    'use strict';

    const body = document.body;
    const offerId = body.dataset.offerId;
    const eventsBase = body.dataset.eventsBase;

    if (!offerId || !eventsBase) {
        return;
    }

    function sessionId() {
        const key = 'affiliate_analytics_session';

        try {
            let value = localStorage.getItem(key);

            if (!value) {
                value = typeof crypto.randomUUID === 'function'
                    ? crypto.randomUUID()
                    : `${Date.now()}-${Math.random().toString(16).slice(2)}`;
                localStorage.setItem(key, value);
            }

            return value;
        } catch (_error) {
            return null;
        }
    }

    function payload() {
        const data = new FormData();
        data.append('offer_id', offerId);

        if (body.dataset.campaign) {
            data.append('campaign', body.dataset.campaign);
        }

        if (body.dataset.source) {
            data.append('source', body.dataset.source);
        }

        const anonymousSession = sessionId();

        if (anonymousSession) {
            data.append('session_id', anonymousSession);
        }

        return data;
    }

    fetch(`${eventsBase}/view.php`, {
        method: 'POST',
        body: payload(),
        keepalive: true,
        credentials: 'same-origin',
    }).catch(() => {});

    document.querySelector('[data-affiliate-link]')?.addEventListener('click', () => {
        const url = `${eventsBase}/click.php`;
        const data = payload();

        if (typeof navigator.sendBeacon === 'function' && navigator.sendBeacon(url, data)) {
            return;
        }

        fetch(url, {
            method: 'POST',
            body: data,
            keepalive: true,
            credentials: 'same-origin',
        }).catch(() => {});
    });
})();

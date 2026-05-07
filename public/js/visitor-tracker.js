// Visitor tracker. Drop into any page with:
//   <script src="https://your-host/js/visitor-tracker.js"></script>
//
// Collects ip + city (via ipapi.co) and a coarse device class, then POSTs the
// payload to /api/track-visit on this same host. ipapi.co failures must not
// break the host page, so the request is best-effort.

(function () {
    'use strict';

    var endpoint = '/api/track-visit';
    var ua = navigator.userAgent || '';
    var device = /Mobi|Android|iPhone|iPad|iPod/i.test(ua) ? 'Mobile' : 'Desktop';

    function send(payload) {
        try {
            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                keepalive: true,
            }).catch(function () { /* ignore */ });
        } catch (e) { /* ignore */ }
    }

    fetch('https://ipapi.co/json/')
        .then(function (r) { return r.ok ? r.json() : {}; })
        .catch(function () { return {}; })
        .then(function (data) {
            send({
                ip: data.ip || null,
                city: data.city || null,
                country: data.country_name || data.country || null,
                device: device,
                user_agent: ua.slice(0, 500),
            });
        });
})();

/**
 * The ajax layer of the dc-general back end scripts.
 *
 * MooTools is on its way out of the Contao back end, so everything the dc-general sends goes through the
 * helpers below, which are built on "fetch". They cover what the MooTools "Request.Contao" did for us:
 * normalising the response and swapping widget markup.
 */
(function () {
    'use strict';

    window.DcGeneral = window.DcGeneral || {};

    /**
     * Send a fire and forget request to a back end route.
     *
     * Used where the server only has to record a state change and the answer is of no interest.
     *
     * Pass {redirect: 'manual'} for routes answering with a redirect that must not be walked - "fetch"
     * follows redirects by default, which would pull in a whole page for nothing.
     *
     * @param {string} url     The URL to request.
     * @param {object} options Additional options for "fetch".
     *
     * @returns {Promise<Response>}
     */
    window.DcGeneral.get = function (url, options) {
        return fetch(url, Object.assign({
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }, options || {}));
    };

    /**
     * Post form data to a back end route and resolve with the normalised response.
     *
     * Contao answers either with JSON or with plain HTML; the latter is normalised to {content: html},
     * which is what "Request.Contao" did as well. A redirect requested via the "X-Ajax-Location" header
     * is followed instead of resolving.
     *
     * @param {string} url  The URL to post to.
     * @param {object} data The payload as plain object.
     *
     * @returns {Promise<{content: string, javascript: string|undefined}>}
     */
    window.DcGeneral.post = function (url, data) {
        var body = new URLSearchParams();

        Object.keys(data).forEach(function (key) {
            body.append(key, data[key]);
        });

        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body
        }).then(function (response) {
            var location = response.headers.get('X-Ajax-Location');

            if (location) {
                window.location.replace(location);

                return { content: '' };
            }

            return response.text().then(function (text) {
                try {
                    return JSON.parse(text);
                } catch (exception) {
                    return { content: text };
                }
            });
        });
    };

    /**
     * Run a piece of javascript that came in as its own field of the response.
     *
     * Contao answers some routes with {content: …, javascript: …}; the latter never passes through
     * setHtml() and has to be executed on its own. This is what the MooTools "Browser.exec()" did.
     *
     * @param {string} code The javascript to run.
     *
     * @returns {void}
     */
    window.DcGeneral.runScript = function (code) {
        if (!code) {
            return;
        }

        var script = document.createElement('script');
        script.textContent = code;
        document.body.appendChild(script);
        script.remove();
    };

    /**
     * Replace the content of an element and run the scripts the new markup brings along.
     *
     * Scripts inserted via innerHTML are inert, so they are recreated. Stimulus controllers in the new
     * markup connect on their own.
     *
     * @param {Element} element The element to fill.
     * @param {string}  html    The new markup.
     *
     * @returns {void}
     */
    window.DcGeneral.setHtml = function (element, html) {
        element.innerHTML = html;

        element.querySelectorAll('script').forEach(function (script) {
            var replacement = document.createElement('script');

            Array.prototype.forEach.call(script.attributes, function (attribute) {
                replacement.setAttribute(attribute.name, attribute.value);
            });

            replacement.textContent = script.textContent;
            script.replaceWith(replacement);
        });
    };
})();

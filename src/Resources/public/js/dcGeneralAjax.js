/**
 * Vanilla replacement for the MooTools "Request.Contao" calls of the picker widgets.
 *
 * MooTools is on its way out of the Contao back end, so the widget templates of dc-general do their ajax
 * handling with "fetch". The two helpers below cover what "Request.Contao" did for us: normalising the
 * response and swapping widget markup.
 */
(function () {
    'use strict';

    window.DcGeneral = window.DcGeneral || {};

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

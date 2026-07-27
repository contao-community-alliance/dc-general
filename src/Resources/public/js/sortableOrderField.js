/**
 * Mirror the drag & drop order of a selection list into a dedicated order field.
 *
 * Contao 5 sorts such lists with the "contao--sortable" Stimulus controller and maintains the value field with
 * "contao--input-map". Both only ever touch a single input, while the widgets of dc-general also support the legacy
 * "orderField" option, which requires a second hidden input to be kept in sync.
 *
 * A list opts in by pointing to the id of that input:
 *
 *     <ul data-controller="contao--sortable" data-cca-order-field="ctrl_myField__sort"> ... </ul>
 *
 * The listeners are delegated on the document, so lists that are rendered later on - the pickers replace the whole
 * widget markup after a selection has been made - are covered as well.
 */
(function () {
    'use strict';

    var ATTRIBUTE = 'data-cca-order-field';
    var SELECTOR = 'ul[' + ATTRIBUTE + ']';

    /**
     * Write the current item order of the list into its order field.
     *
     * @param {Element|null} list The list to read the order from.
     */
    function sync(list) {
        if (!list) {
            return;
        }

        var orderField = document.getElementById(list.getAttribute(ATTRIBUTE));

        if (!orderField) {
            return;
        }

        orderField.value = Array.prototype.map.call(
            list.querySelectorAll(':scope > [data-id]'),
            function (item) {
                return item.getAttribute('data-id');
            }
        ).join(',');
    }

    /**
     * Determine the opted in list the passed element belongs to.
     *
     * @param {EventTarget|null} element The element to start the lookup at.
     *
     * @returns {Element|null}
     */
    function listOf(element) {
        return (element && element.closest) ? element.closest(SELECTOR) : null;
    }

    /**
     * Synchronize all lists of the current document.
     */
    function syncAll() {
        Array.prototype.forEach.call(document.querySelectorAll(SELECTOR), sync);
    }

    // Sorting: the controller dispatches the event on the moved item, from where it bubbles up to the document.
    document.addEventListener('contao--sortable:update', function (event) {
        sync(listOf(event.target));
    });

    // Removing: the input map detaches the item from the list, therefore the order can only be read afterwards.
    document.addEventListener('click', function (event) {
        var list = listOf(event.target);

        if (list) {
            window.setTimeout(function () {
                sync(list);
            }, 0);
        }
    }, true);

    // Initial state - the value may contain items the order field does not know about yet.
    if ('loading' === document.readyState) {
        document.addEventListener('DOMContentLoaded', syncAll);
    } else {
        syncAll();
    }

    document.documentElement.addEventListener('turbo:render', syncAll);
})();

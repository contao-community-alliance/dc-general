/**
 * This file is part of contao-community-alliance/dc-general.
 *
 * (c) 2013-2024 Contao Community Alliance.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * Provide methods to handle back end tasks.
 * Special functions for DC_General
 *
 * @package    contao-community-alliance/dc-general
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2013-2024 Contao Community Alliance.
 * @license    https://github.com/contao-community-alliance/dc-general/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

var BackendGeneral =
  {
    loadSubTree: function (el, data) {
      el.blur();

      var id    = data.toggler,
          level = data.level,
          item  = document.getElementById(id),
          image = el.querySelector('img');

      data.action        = 'DcGeneralLoadSubTree';
      data.REQUEST_TOKEN = Contao.request_token;

      // The sub tree is already loaded - only fold it and persist the state.
      if (item) {
        if ('none' === window.getComputedStyle(item).display) {
          item.style.display = 'inline';
          image.src = image.src.replace('folPlus.svg', 'folMinus.svg');
          el.title = Contao.lang.collapse;
          el.classList.add('foldable--open');
        } else {
          item.style.display = 'none';
          image.src = image.src.replace('folMinus.svg', 'folPlus.svg');
          el.title = Contao.lang.expand;
          el.classList.remove('foldable--open');
        }
        DcGeneral.post(data.url, data);

        return false;
      }

      AjaxRequest.displayBox(Contao.lang.loading + ' …');
      DcGeneral.post(data.url, data).then(function (response) {
        var li = document.createElement('li');
        li.id = id;
        li.className = 'parent';
        li.style.display = 'inline';

        var ul = document.createElement('ul');
        ul.className = 'level_' + level;
        li.appendChild(ul);
        // setHtml() runs the scripts of the answer, which "evalScripts: true" did before.
        DcGeneral.setHtml(ul, response.content);

        // Both branches of the former "mode" check did the same thing.
        el.closest('li').after(li);

        // Update the referer ID
        li.querySelectorAll('a').forEach(function (link) {
          link.href = link.href.replace(/&ref=[a-f0-9]+/, '&ref=' + Contao.referer_id);
        });

        el.title = Contao.lang.collapse;
        image.src = image.src.replace('folPlus.svg', 'folMinus.svg');
        el.classList.add('foldable--open');
        window.dispatchEvent(new CustomEvent('structure'));
        AjaxRequest.hideBox();

        // HOOK - Contao still fires this one through MooTools itself, see its toggle-nodes controller.
        window.fireEvent('ajax_change');
      });

      return false;
    },

    /**
     * Toggle the line wrap of a textarea.
     *
     * Contao dropped Backend.toggleWrap() with version 5 without a replacement - only the
     * "toggleWrap" css class is left over. The dc-general still offers the button, so the
     * behaviour lives here now.
     *
     * @param {string} id The id of the textarea.
     *
     * @returns {boolean}
     */
    toggleWrap: function (id) {
      const textarea = document.getElementById(id);

      if (textarea) {
        textarea.wrap = ('off' === textarea.wrap) ? 'soft' : 'off';
      }

      return false;
    },

    /**
     * Display the message
     *
     * @param {string} message      The message text
     * @param {boolean} loading     If display loading indicator.
     * @param {string} messageClass The css class for the box.
     *
     * @returns {void}
     */
    displayMessage: function (message, loading, messageClass) {
      var box = document.getElementById('general_messageBox'),
        overlay = document.getElementById('general_messageOverlay'),
        scrollY = window.scrollY;

      if (null === overlay) {
        overlay = document.createElement('div');
        overlay.id = 'general_messageOverlay';
        document.body.appendChild(overlay);
      }

      overlay.style.display = 'block';
      overlay.style.top = scrollY + 'px';

      if (null === box) {
        box = document.createElement('div');
        box.id = 'general_messageBox';
        document.body.appendChild(box);
      }

      box.innerHTML = message;
      box.style.display = 'block';
      box.style.top = (scrollY + 100) + 'px';

      if (messageClass) {
        box.classList.add(messageClass);
      }

      if (loading) {
        box.classList.add('loading');
      }
    },

    /**
     * Hide the message
     *
     * @returns {void}
     */
    hideMessage: function () {
      var box = document.getElementById('general_messageBox'),
        overlay = document.getElementById('general_messageOverlay');

      if (overlay) {
        overlay.style.display = 'none';
        overlay.remove();
      }

      if (box) {
        box.style.display = 'none';
        box.remove();
      }
    },

    /**
     * Confirm if select an element or property for override/edit all.
     *
     * @param {object} submit    The DOM submit element.
     * @param {string} selection The DOM name for selection.
     * @param {string} message   The confirm message.
     *
     * @returns {boolean}
     */
    confirmSelectOverrideEditAll: function (submit, selection, message) {
      submit.blur();

      var form = submit.form;
      var collection = form.elements[selection];

      // "collection" is a single element when only one checkbox carries the name, a RadioNodeList otherwise.
      var isSelected = Array.prototype.some.call(
        (collection && undefined !== collection.length) ? collection : [collection].filter(Boolean),
        function (element) {
          return element.checked;
        }
      );

      if (isSelected) {
        if (submit.name === 'delete') {
          return true;
        }

        submit.onclick = '';
        submit.click();

        return true;
      }

      this.displayMessage(message, false, 'box-small');

      return false;
    },

    /**
     * Confirm if select an element for delete all.
     *
     * @param {object} submit          The DOM submit element.
     * @param {string} selection       The DOM name for selection.
     * @param {string} message         The confirm message.
     * @param {string} messageDelete   The confirm message for delete.
     * @param {string} confirmOk       The confirm ok for delete.
     * @param {string} confirmAbort    The confirm abort for delete.
     *
     * @returns {boolean}
     */
    confirmSelectDeleteAll: function (submit, selection, message, messageDelete, confirmOk, confirmAbort) {
      submit.blur();

      var isSelected = this.confirmSelectOverrideEditAll(submit, selection, message);

      if (!isSelected) {
        return false;
      }

      this.confirmDelete(submit, messageDelete, confirmOk, confirmAbort);

      return true;
    },

    /**
     * Confirm for delete.
     *
     * @param {object} submit       The DOM submit element.
     * @param {string} message      The confirm message.
     * @param {string} confirmOk    The text for the confirm button ok.
     * @param {string} confirmAbort The text for the confirm button abort.
     *
     * @returns {boolean}
     */
    confirmDelete: function (submit, message, confirmOk, confirmAbort) {
      var confirmContainer = document.createElement('div');

      var confirmMessage = document.createElement('h2');
      confirmMessage.className = 'tl_info';
      confirmMessage.innerHTML = message;
      confirmContainer.appendChild(confirmMessage);
      confirmContainer.appendChild(document.createElement('p'));

      var submitContainer = document.createElement('div');
      submitContainer.className = 'tl_submit_container';
      confirmContainer.appendChild(submitContainer);

      var makeButton = function (suffix, value) {
        var button = document.createElement('input');
        button.id = submit.name + suffix;
        button.name = submit.name + suffix;
        button.value = value;
        button.type = 'submit';
        button.className = 'tl_submit';
        submitContainer.appendChild(button);

        return button;
      };

      var confirmButtonOk = makeButton('Ok', confirmOk);
      var confirmButtonAbort = makeButton('Abort', confirmAbort);

      // The markup is handed over as html, so the buttons have to be looked up again afterwards.
      this.displayMessage(confirmContainer.innerHTML, false, 'box-small');

      document.getElementById(confirmButtonOk.id).addEventListener('click', function () {
        submit.onclick = '';
        submit.click();
      });

      document.getElementById(confirmButtonAbort.id).addEventListener('click', function () {
        BackendGeneral.hideMessage();
      });

      return true;
    },

    autoSubmit: function (tableName) {
      window.dispatchEvent(new Event('store-scroll-offset'));
      var element = document.createElement('input');
      element.type = 'hidden';
      element.name = 'SUBMIT_TYPE';
      element.value = 'auto';

      var form = ('string' === typeof tableName ? document.getElementById(tableName) : null) || tableName;
      form.appendChild(element);
      form.noValidate = !0;
      form.mustRedirect = false;
      form.requestSubmit();
    }
  };

window.addEventListener('DOMContentLoaded', function () {
  // Expand fieldset for required fields.
  document.querySelectorAll('.collapsed:has(*[required])').forEach(function(el) {
    el.classList.remove('collapsed');
  });
});

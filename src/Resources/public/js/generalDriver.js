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

/**
 * Walk the siblings in the given direction and return the first one matching the selector.
 *
 * MooTools' getPrevious()/getNext() skipped non matching siblings; "previousElementSibling" does not,
 * so the search is done here.
 *
 * @param {Element} element   The element to start from.
 * @param {string}  selector  The selector to match.
 * @param {string}  direction "previousElementSibling" or "nextElementSibling".
 *
 * @returns {Element|null}
 */
function siblingMatching(element, selector, direction) {
  for (var sibling = element[direction]; sibling; sibling = sibling[direction]) {
    if (sibling.matches(selector)) {
      return sibling;
    }
  }

  return null;
}

/**
 * Return the first direct child matching the selector - the counterpart of MooTools' getFirst().
 *
 * @param {Element} element  The parent element.
 * @param {string}  selector The selector to match.
 *
 * @returns {Element|null}
 */
function firstChildMatching(element, selector) {
  return element ? element.querySelector(':scope > ' + selector) : null;
}

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
     * Toggle the visibility of an element
     *
     * @param {object} el            The DOM element
     * @param {string} icon          The icon enabled
     * @param {string} icon_disabled The icon disabled
     *
     * @returns {boolean}
     */
    toggleVisibility: function (el, icon, icon_disabled) {
      el.blur();

      // Set default as "eye".
      if (!icon) {
        icon = 'visible.svg';
      }

      if (!icon_disabled) {
        icon_disabled = 'invisible.svg';
      }

      // Get images - we have optional two icons:
      // the first is for dark mode and second for light mode.
      const imageList  = el.getElementsByTagName('img');
      const darkImage  = (imageList.length > 1) ? imageList[0] : null;
      const lightImage = imageList[imageList.length - 1]

      // Generate images for dark mode. Both variants are always in the markup, the color scheme
      // only decides which one CSS shows - so the suffix must not depend on the active scheme.
      // Deriving it from "document.documentElement.dataset.colorScheme" left the dark icon
      // untouched in light mode, because "invisible.svg" does not occur in "invisible--dark.svg".
      const suffixDarkImage = (icon) => {
        let posDot = icon.lastIndexOf('.');
        return icon.slice(0, posDot) + '--dark.' + icon.slice(posDot + 1);
      };

      const iconLight = icon;
      const iconDark  = darkImage ? suffixDarkImage(icon) : null;
      const iconLight_disabled = icon_disabled;
      const iconDark_disabled  = darkImage ? suffixDarkImage(icon_disabled) : null;

      let img = null,
        publish = (lightImage.src.indexOf(iconLight_disabled) !== -1),
        div = el.closest('div'),
        next,
        listIcon;

      // No progress box here - toggling has to feel instant, like it does in the Contao core. The route
      // answers with a redirect that is of no interest, so it is not followed.
      DcGeneral.get(
        el.href + (el.href.indexOf('?') === -1 ? '?' : '&') + 'state=' + (publish ? 1 : 0),
        {redirect: 'manual'}
      )
        .then(function () {
          // Find the icon depending on the view (tree view, list view, parent view)
          if (div.classList.contains('tl_right')) {
            img = siblingMatching(div, 'div', 'previousElementSibling')
              ?.querySelector('div.tl_pagetree_content')?.querySelector('img') ?? null;
          } else if (div.classList.contains('tl_listing_container')) {
            const previousCell = siblingMatching(el.closest('td'), 'td', 'previousElementSibling');
            img = firstChildMatching(previousCell, 'div.list_icon');
            if (img == null) { // Comments
              img = previousCell?.querySelector('div.cte_type') ?? null;
            }
            if (img == null) { // showColumns
              img = firstChildMatching(el.closest('tr'), 'td')?.querySelector('div.list_icon_new') ?? null;
            }
          } else if ((next = siblingMatching(div, 'div', 'nextElementSibling')) && next.classList.contains('cte_type')) {
            img = next;
          }

          // Provide change the list icon for example by newsletter recipients.
          if (
            (img === null)
            && (listIcon = el.parentElement?.parentElement?.querySelector('div.list_icon')?.parentElement)
          ) {
            img = listIcon;
          }

          // Change the icon
          if (img != null) {
            // Tree view
            if (img.nodeName.toLowerCase() === 'img') {
              if (img.closest('ul.tl_listing')?.classList.contains('tl_tree_xtnd')) {
                if (publish) {
                  img.src = img.src.replace(/_1\.(gif|png|jpe?g|svg)/, '.$1');
                } else {
                  img.src = img.src.replace(/\.(gif|png|jpe?g|svg)/, '_1.$1');
                }
              } else {
                if (img.src.match(/folPlus|folMinus/)) {
                  const nextLink = siblingMatching(img.closest('a'), 'a', 'nextElementSibling');
                  if (nextLink) {
                    img = firstChildMatching(nextLink, 'img');
                  } else {
                    img = document.createElement('img'); // no icons used (see #2286)
                  }
                }
                var index;
                if (publish) {
                  index = img.src.replace(/.*_([0-9])\.(gif|png|jpe?g|svg)/, '$1');
                  img.src = img.src.replace(/_[0-9]\.(gif|png|jpe?g|svg)/, ((parseInt(index, 10) === 1) ? '' : '_' + (parseInt(index, 10) - 1)) + '.$1');
                } else {
                  index = img.src.replace(/.*_([0-9])\.(gif|png|jpe?g|svg)/, '$1');
                  img.src = img.src.replace(/(_[0-9])?\.(gif|png|jpe?g|svg)/, ((index === img.src) ? '_1' : '_' + (parseInt(index, 10) + 1)) + '.$2');
                }
              }
            }
            // Parent view
            else if (img.classList.contains('cte_type')) {
              if (publish) {
                img.classList.add('published');
                img.classList.remove('unpublished');
              } else {
                img.classList.add('unpublished');
                img.classList.remove('published');
              }
            }
            // List view
            else {
              const background = window.getComputedStyle(img).backgroundImage;
              if (publish) {
                img.style.backgroundImage = background.replace(/_\.(gif|png|jpe?g)/, '.$1');
              } else {
                img.style.backgroundImage = background.replace(/\.(gif|png|jpe?g)/, '_.$1');
              }
            }
          }

          // Send request
          if (publish) {
            lightImage.src = lightImage.src.replace(iconLight_disabled, iconLight);
            if (darkImage) {
              darkImage.src = darkImage.src.replace(iconDark_disabled, iconDark);
            }
          } else {
            lightImage.src = lightImage.src.replace(iconLight, iconLight_disabled);
            if (darkImage) {
              darkImage.src = darkImage.src.replace(iconDark, iconDark_disabled);
            }
          }
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

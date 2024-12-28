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
	loadSubTree: function(el, data)
	{
		el.blur();

		var id    = data.toggler,
			level = data.level,
			mode  = data.mode,
			item  = $(id),
			image = $(el).getFirst('img');

		data.action = 'DcGeneralLoadSubTree';
		data.REQUEST_TOKEN = Contao.request_token;

		if (item) {
			if (item.getStyle('display') == 'none') {
				item.setStyle('display', 'inline');
				image.src = image.src.replace('folPlus.svg', 'folMinus.svg');
				$(el).store('tip:title', Contao.lang.collapse);
				new Request.Contao({url: data.url, field:el}).post(data);
			} else {
				item.setStyle('display', 'none');
				image.src = image.src.replace('folMinus.svg', 'folPlus.svg');
				$(el).store('tip:title', Contao.lang.expand);
				new Request.Contao({url: data.url, field:el}).post(data);
			}
			return false;
		}

		new Request.Contao({
			url: data.url,
			field: el,
			evalScripts: true,
			onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
			onSuccess: function(txt, json) {
				var li = new Element('li', {
					'id': id,
					'class': 'parent',
					'styles': {
						'display': 'inline'
					}
				});

				var ul = new Element('ul', {
					'class': 'level_' + level,
					'html': txt
				}).inject(li, 'bottom');

				if (mode == 5) {
					li.inject($(el).getParent('li'), 'after');
				} else {
					var parent = $(el).getParent('li');
					li.inject(parent, 'after');
				}

				// Update the referer ID
				li.getElements('a').each(function(el) {
					el.href = el.href.replace(/&ref=[a-f0-9]+/, '&ref=' + Contao.referer_id);
				});

				$(el).store('tip:title', Contao.lang.collapse);
				image.src = image.src.replace('folPlus.svg', 'folMinus.svg');
				window.fireEvent('structure');
				AjaxRequest.hideBox();

				// HOOK
				window.fireEvent('ajax_change');
			}
		}).post(data);

		return false;
	},

	/**
	 * Toggle the visibility of an element
	 *
	 * @param {object} el    The DOM element
	 *
	 * @returns {boolean}
	 */
	toggleVisibility: function(el, icon, icon_disabled) {
		el.blur();

		if(!icon) {
			icon = 'visible.svg';
		}

		if(!icon_disabled) {
			icon_disabled = 'invisible.svg';
		}

		var img = null,
			image = $(el).getFirst('img'),
			publish = (image.src.indexOf(icon_disabled) != -1),
			div = el.getParent('div'),
			next,
			listIcon;

		new Request.Contao({
			'url':$(el).href,
			'followRedirects':false,
			'onSuccess': function() {

				// Find the icon depending on the view (tree view, list view, parent view)
				if (div.hasClass('tl_right')) {
					img = div.getPrevious('div').getElement('img');
				} else if (div.hasClass('tl_listing_container')) {
					img = el.getParent('td').getPrevious('td').getFirst('div.list_icon');
					if (img == null) { // Comments
						img = el.getParent('td').getPrevious('td').getElement('div.cte_type');
					}
					if (img == null) { // showColumns
						img = el.getParent('tr').getFirst('td').getElement('div.list_icon_new');
					}
				} else if ((next = div.getNext('div')) && next.hasClass('cte_type')) {
					img = next;
				}

				// Provide change the list icon for example by newsletter recipients.
				if ((img === null)
					&& (listIcon = el.getParent().getParent().getElements('div.list_icon').getFirst().getParent())) {
					img = listIcon[0];
				}

				// Change the icon
				if (img != null) {
					// Tree view
					if (img.nodeName.toLowerCase() == 'img') {
						if (img.getParent('ul.tl_listing').hasClass('tl_tree_xtnd')) {
							if (publish) {
								img.src = img.src.replace(/_1\.(gif|png|jpe?g|svg)/, '.$1');
							} else {
								img.src = img.src.replace(/\.(gif|png|jpe?g|svg)/, '_1.$1');
							}
						} else {
							if (img.src.match(/folPlus|folMinus/)) {
								if (img.getParent('a').getNext('a')) {
									img = img.getParent('a').getNext('a').getFirst('img');
								} else {
									img = new Element('img'); // no icons used (see #2286)
								}
							}
							var index;
							if (publish) {
								index = img.src.replace(/.*_([0-9])\.(gif|png|jpe?g|svg)/, '$1');
								img.src = img.src.replace(/_[0-9]\.(gif|png|jpe?g|svg)/, ((index.toInt() == 1) ? '' : '_' + (index.toInt() - 1)) + '.$1');
							} else {
								index = img.src.replace(/.*_([0-9])\.(gif|png|jpe?g|svg)/, '$1');
								img.src = img.src.replace(/(_[0-9])?\.(gif|png|jpe?g|svg)/, ((index == img.src) ? '_1' : '_' + (index.toInt() + 1)) + '.$2');
							}
						}
					}
					// Parent view
					else if (img.hasClass('cte_type')) {
						if (publish) {
							img.addClass('published');
							img.removeClass('unpublished');
						} else {
							img.addClass('unpublished');
							img.removeClass('published');
						}
					}
					// List view
					else {
						if (publish) {
							img.setStyle('background-image', img.getStyle('background-image').replace(/_\.(gif|png|jpe?g|svg)/, '.$1'));
						} else {
							img.setStyle('background-image', img.getStyle('background-image').replace(/\.(gif|png|jpe?g|svg)/, '_.$1'));
						}
					}
				}

				// Send request
				if (publish) {
					image.src = image.src.replace(icon_disabled, icon);
				} else {
					image.src = image.src.replace(icon, icon_disabled);
				}
			}
		}).get({'state': (publish ? 1 : 0)});

		return false;
	},

	/**
	 * Set the visibility of a legend.
	 *
	 * @param {object} el     The DOM element
	 * @param {string} legend The ID of the legend element
	 * @param {string} table  The table name
	 *
	 * @returns {boolean}
	 */
	setLegendState: function(el, legend, table) {
		el.blur();
		var fs = $('pal_' + legend);

		if (fs.hasClass('collapsed')) {
			fs.removeClass('collapsed');
			new Request.Contao().post({'action':'setLegendState', 'legend':legend, 'table':table, 'state':1, 'REQUEST_TOKEN':Contao.request_token});
		} else {
			fs.addClass('collapsed');
			new Request.Contao().post({'action':'setLegendState', 'legend':legend, 'table':table, 'state':0, 'REQUEST_TOKEN':Contao.request_token});
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
        var box = $('general_messageBox'),
            overlay = $('general_messageOverlay'),
            scroll = window.getScroll();

        if (overlay === null) {
            overlay = new Element('div', {
                'id': 'general_messageOverlay'
            }).inject($(document.body), 'bottom');
        }

        overlay.set({
            'styles': {
                'display': 'block',
                'top': scroll.y + 'px'
            }
        });

        if (box === null) {
            box = new Element('div', {
                'id': 'general_messageBox'
            }).inject($(document.body), 'bottom');
        }

        box.set({
            'html': message,
            'styles': {
                'display': 'block',
                'top': (scroll.y + 100) + 'px'
            }
        });

        if (messageClass) {
            box.addClass(messageClass);
        }

        if (loading) {
            box.addClass('loading');
        }
    },

    /**
     * Hide the message
     *
     * @returns {void}
     */
    hideMessage: function () {
        var box = $('general_messageBox'),
            overlay = $('general_messageOverlay');

        if (overlay) {
            overlay.setStyle('display', 'none');
        }

        if (box) {
            box.setStyle('display', 'none');
        }

        overlay.remove();
        box.remove();
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

        var isSelected = false;
        $$(collection).each(function (element) {
            if (isSelected || !element.checked) {
                return true;
            }

            isSelected = true;
        });

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
        var confirmContainer = new Element('div');

        var confirmMessage = new Element('h2', {
            'html': message,
            'class': 'tl_info'
        }).inject(confirmContainer, 'bottom');
        var confirmSpace = new Element('p').inject(confirmContainer, 'bottom');

        var submitContainer = new Element('div', {
            'class': 'tl_submit_container'
        }).inject(confirmContainer, 'bottom');

        var confirmButtonOk = new Element('input', {
            'id': submit.name + 'Ok',
            'name': submit.name + 'Ok',
            'value': confirmOk,
            'type': 'submit',
            'class': 'tl_submit'
        }).inject(submitContainer, 'bottom');

        var confirmButtonAbort = new Element('input', {
            'id': submit.name + 'Abort',
            'name': submit.name + 'Abort',
            'value': confirmAbort,
            'type': 'submit',
            'class': 'tl_submit'
        }).inject(submitContainer, 'bottom');

        this.displayMessage(confirmContainer.get('html'), false, 'box-small');

        $(confirmButtonOk.id).addEvent('click', function () {
            submit.onclick = '';
            submit.click();
        });

        $(confirmButtonAbort.id).addEvent('click', function () {
            BackendGeneral.hideMessage();
        });

        return true;
    }
};

/**
 * Class Backend
 *
 * Provide methods to handle back end tasks.
 * Special functions for DC_General
 *
 * @copyright  The MetaModels team.
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
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
				image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
				$(el).store('tip:title', Contao.lang.collapse);
				new Request.Contao({field:el}).post(data);
			} else {
				item.setStyle('display', 'none');
				image.src = image.src.replace('folMinus.gif', 'folPlus.gif');
				$(el).store('tip:title', Contao.lang.expand);
				new Request.Contao({field:el}).post(data);
			}
			return false;
		}

		new Request.Contao({
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
				image.src = image.src.replace('folPlus.gif', 'folMinus.gif');
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
			icon = 'visible.gif';
		}

		if(!icon_disabled) {
			icon_disabled = 'invisible.gif';
		}

		var img = null,
			image = $(el).getFirst('img'),
			publish = (image.src.indexOf(icon_disabled) != -1),
			div = el.getParent('div'),
			next;

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

				// Change the icon
				if (img != null) {
					// Tree view
					if (img.nodeName.toLowerCase() == 'img') {
						if (img.getParent('ul.tl_listing').hasClass('tl_tree_xtnd')) {
							if (publish) {
								img.src = img.src.replace(/_1\.(gif|png|jpe?g)/, '.$1');
							} else {
								img.src = img.src.replace(/\.(gif|png|jpe?g)/, '_1.$1');
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
								index = img.src.replace(/.*_([0-9])\.(gif|png|jpe?g)/, '$1');
								img.src = img.src.replace(/_[0-9]\.(gif|png|jpe?g)/, ((index.toInt() == 1) ? '' : '_' + (index.toInt() - 1)) + '.$1');
							} else {
								index = img.src.replace(/.*_([0-9])\.(gif|png|jpe?g)/, '$1');
								img.src = img.src.replace(/(_[0-9])?\.(gif|png|jpe?g)/, ((index == img.src) ? '_1' : '_' + (index.toInt() + 1)) + '.$2');
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
							img.setStyle('background-image', img.getStyle('background-image').replace(/_\.(gif|png|jpe?g)/, '.$1'));
						} else {
							img.setStyle('background-image', img.getStyle('background-image').replace(/\.(gif|png|jpe?g)/, '_.$1'));
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
	}
};

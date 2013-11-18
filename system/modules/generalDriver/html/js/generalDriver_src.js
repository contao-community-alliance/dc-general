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
					var folder = false,
						parent = $(el).getParent('li');

					while (typeOf(parent) == 'element' && (next = parent.getNext('li'))) {
						parent = next;
						if (parent.hasClass('tl_folder')) {
							folder = true;
							break;
						}
					}

					if (folder) {
						li.inject(parent, 'before');
					} else {
						li.inject(parent, 'after');
					}
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
	 * Make parent view items sortable
	 * @param object
	 */
	makeParentViewSortable: function(ul, cdp, pdp)
	{
		var list = new Sortables(ul,
		{
			contstrain: true,
			opacity: 0.6
		});

		list.active = false;

		list.addEvent('start', function()
		{
			list.active = true;
		});

		list.addEvent('complete', function(el)
		{
			if (!list.active)
			{
				return;
			}
			
			var href = window.location.href.replace(/\?.*$/, '');
			var req = window.location.search;
			req += '&act=cut';
			req += '&source=' +el.get('id').replace(/li_/, '');
			req += '&pdp=' + pdp;
			req += '&cdp=' + cdp;
		
			if (el.getPrevious())
			{
				req += '&mode=1';
				req += '&after=' + el.getPrevious().get('id').replace(/li_/, '');
			}
			else if (el.getParent())
			{
				req += '&mode=2';
				req += '&after=start';
			}
			
			new Request.Contao({
				url:href+req
			}).get();

		}.bind(cdp).bind(pdp));
	}
};

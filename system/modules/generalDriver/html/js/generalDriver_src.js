/**
 * Class Backend
 *
 * Provide methods to handle back end tasks.
 * Special functions for DC_General
 * 
 * @copyright  The MetaModels team.
 * @author     Stefan Heimes <cms@men-at-work.de>
 */

var BackendGeneral =
{
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
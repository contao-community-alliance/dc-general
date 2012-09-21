/**
 * Class Backend
 *
 * Provide methods to handle back end tasks.
 * @copyright  Leo Feyer 2005-2011
 * @author     Leo Feyer <http://www.contao.org>
 * @package    Backend
 */
var BackendGeneral =
{

	/**
	 * Make parent view items sortable
	 * @param object
	 */
	makeParentViewSortable: function(ul, cdp, pdv)
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

    		if (el.getPrevious())
    		{
    			var id = el.get('id').replace(/li_/, '');
    			var pid = el.getPrevious().get('id').replace(/li_/, '');
    			var req = window.location.search.replace(/id=[0-9]*/, 'id=' + id) + '&act=cut&mode=1&pid=' + pid;
    			var href = window.location.href.replace(/\?.*$/, '');
                console.log({url:href+req});                
//    			new Request.Contao({url:href+req}).get();
    		}
    		else if (el.getParent())
    		{
    			var id = el.get('id').replace(/li_/, '');
    			var pid = el.getParent().get('id').replace(/ul_/, '');
    			var req = window.location.search.replace(/id=[0-9]*/, 'id=' + id) + '&act=cut&mode=2&pid=' + pid;
    			var href = window.location.href.replace(/\?.*$/, '');
                console.log({url:href+req});
//    			new Request.Contao({url:href+req}).get();
    		}
    	}).bind(cdp).bind(pdv);
	}
};

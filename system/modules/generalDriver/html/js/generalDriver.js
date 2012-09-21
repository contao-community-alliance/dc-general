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
            
            
            var req = window.location.search;
            req += '&act=cut';
            if (el.getPrevious())
            {
                req += '&mode=1';
            }
            else
            {
                req += '&mode=2';
            }
            
            req += '&source=' +el.get('id').replace(/li_/, '');
            req += '&after=' + el.getPrevious().get('id').replace(/li_/, '');
            req += '&pdp=' + pdp;
            req += '&cdp=' + cdp;
            var href = window.location.href.replace(/\?.*$/, '');
            new Request.Contao({url:href+req}).get();
            
//            console.log(href+req);                

        //do=metamodels&table=tl_metamodel_dcasetting&id=1&act=cut&mode=1&pid=1&source=5
        //
        //            if (el.getPrevious())
        //            {
        //                var req = window.location.search;
        //                req += '&act=cut';
        //                req += '&mode=1';
        //                req += '&source=' +el.get('id').replace(/li_/, '');
        //                req += '&after=' + el.getPrevious().get('id').replace(/li_/, '');
        //                req += '&pdp=' + pdp;
        //                req += '&cdp=' + cdp;
        //                var href = window.location.href.replace(/\?.*$/, '');
        //                console.log({
        //                    url:href+req
        //                    });                
        //            //    			new Request.Contao({url:href+req}).get();
        //            }
        //            else if (el.getParent())
        //            {
        //                var req = window.location.search;
        //                req += '&act=cut';
        //                req += '&mode=2';
        //                req += '&source=' +el.get('id').replace(/li_/, '');
        //                req += '&after=' + el.getPrevious().get('id').replace(/li_/, '');
        //                req += '&pdp=' + pdp;
        //                req += '&cdp=' + cdp;
        //                var href = window.location.href.replace(/\?.*$/, '');
        //                console.log({
        //                    url:href+req
        //                    });                
        //            //    			new Request.Contao({url:href+req}).get();
        //            }
        }.bind(cdp).bind(pdp));
    }
};

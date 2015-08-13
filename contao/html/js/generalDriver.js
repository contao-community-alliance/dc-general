/**
 * Class Backend
 *
 * Provide methods to handle back end tasks.
 * Special functions for DC_General
 *
 * @copyright  The MetaModels team.
 * @author     Stefan Heimes <cms@men-at-work.de>
 */

var BackendGeneral={makeParentViewSortable:function(b,d,a){var c=new Sortables(b,{contstrain:true,opacity:0.6});c.active=false;c.addEvent("start",function(){c.active=true});c.addEvent("complete",function(f){if(!c.active){return}var e=window.location.href.replace(/\?.*$/,"");var g=window.location.search;g+="&act=cut";g+="&source="+f.get("id").replace(/li_/,"");g+="&pdp="+a;g+="&cdp="+d;if(f.getPrevious()){g+="&mode=1";g+="&after="+f.getPrevious().get("id").replace(/li_/,"")}else{if(f.getParent()){g+="&mode=2";g+="&after=start"}}new Request.Contao({url:e+g}).get()}.bind(d).bind(a))}};
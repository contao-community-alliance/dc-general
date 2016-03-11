/**
 * Tables Drag & Drop
 *  Author: Denis Howlett <feedback@isocra.com>
 *  WWW: http:www.isocra.com/
 *
 * NOTICE: You may use this code for any purpose, commercial or
 * private, without any further permission from the author. You may
 * remove this notice from your final code if you wish, however we
 * would appreciate it if at least the web site address is kept.
 *
 * You may *NOT* re-distribute this code in any way except through its
 * use. That means, you can include it in your product, or your web
 * site, or any other form where the code is actually being used. You
 * may not put the plain javascript up on your site for download or
 * include it in your javascript libraries for download.
 * If you wish to share this code with others, please just point them
 * to the URL instead.
 *
 * Please DO NOT link directly to this .js files from your site. Copy
 * the files to your server and use them there. Thank you.
 */

/** Keep hold of the current table being dragged */
var currenttable = null;

/**
 * Capture the onmousemove so that we can see if a row from the current table if any is being dragged.
 *
 * @param ev the event (for Firefox and Safari, otherwise we use window.event for IE)
 */
document.onmousemove = function (ev)
{
	if (currenttable && currenttable.dragObject)
	{
		ev = ev || window.event;
		var mousePos = currenttable.mouseCoords(ev);
		var y = mousePos.y - currenttable.mouseOffset.y;
		if (y != currenttable.oldY)
		{
			// work out if we're going up or down...
			var movingDown = y > currenttable.oldY;
			// update the old value
			currenttable.oldY = y;
			// update the style to show we're dragging
			currenttable.dragObject.style.opacity = .5;
			// If we're over a row then move the dragged row to there so that the user sees the
			// effect dynamically
			var currentRow = currenttable.findDropTargetRow(ev);
			if (currentRow)
			{
				if (movingDown && currenttable.dragObject != currentRow)
				{
					currenttable.dragObject.parentNode.insertBefore(currenttable.dragObject, currentRow.nextSibling);
				}
				else if (!movingDown && currenttable.dragObject != currentRow)
				{
					currenttable.dragObject.parentNode.insertBefore(currenttable.dragObject, currentRow);
				}
			}
		}

		return false;
	}
};

// Similarly for the mouseup
document.onmouseup = function (ev)
{
	if (currenttable && currenttable.dragObject)
	{
		var droppedRow = currenttable.dragObject;
		// If we have a dragObject, then we need to release it,
		// The row will already have been moved to the right place so we just reset stuff
		droppedRow.style.opacity = 1;
		currenttable.dragObject = null;
		// And then call the onDrop method in case anyone wants to do any post processing
		currenttable.onDrop(currenttable.table, droppedRow);
		currenttable = null; // let go of the table too
	}
};

/**
 * get the source element from an event in a way that works for IE and Firefox and Safari
 * @param evt the source event for Firefox (but not IE--IE uses window.event)
 */
function getEventSource(evt)
{
	if (window.event)
	{
		evt = window.event; // For IE
		return evt.srcElement;
	}
	else
	{
		return evt.target; // For Firefox
	}
}

/**
 * Encapsulate table Drag and Drop in a class. We'll have this as a Singleton
 * so we don't get scoping problems.
 */
function GeneralTableDnD()
{
	/** Keep hold of the current drag object if any */
	this.dragObject = null;
	/** The current mouse offset */
	this.mouseOffset = null;
	/** The current table */
	this.table = null;
	/** Remember the old value of Y so that we don't do too much processing */
	this.oldY = 0;

	/** Initialise the drag and drop by capturing mouse move events */
	this.init = function (table)
	{
		this.table = table;
		var rows = table.tBodies[0].rows; //getElementsByTagName("tr")
		for (var i = 0; i < rows.length; i++)
		{
			// John Tarr: added to ignore rows that I've added the NoDnD attribute to (Category and Header rows)
			var nodrag = rows[i].getAttribute("NoDrag")
			if (nodrag == null || nodrag == "undefined")
			{ //There is no NoDnD attribute on rows I want to drag
				this.makeDraggable(rows[i]);
			}
		}
	};

	/**
	 * This function is called when you drop a row, so redefine it in your code
	 * to do whatever you want, for example use Ajax to update the server
	 *
	 * @param table Current Table
	 *
	 * @param droppedRow Current row
	 */
	this.onDrop = function (table, droppedRow)
	{
		var id = droppedRow.getAttribute('data-model-id');
		var insertAfter = null;
		var prevElement = GeneralEnvironment.getDom().getPreviousSibling(droppedRow);

		// Check if we have a prev element or the top.
		if (prevElement == null)
		{
			var topId = id.split('::');
			if(topId !== null)
			{
				insertAfter = topId[0] + '::0';
			}
		}
		else
		{
			insertAfter = prevElement.getAttribute('data-model-id');
		}

		// Build url.
		var href = window.location.href.replace(/\?.*$/, '');
		var req = window.location.search;
		req += '&act=paste';
		req += '&source=' + id;
		req += '&after=' + insertAfter;
		req += '&isAjax=1';

		// var href = GeneralEnvironment.getDom().getContaoBase();
		GeneralEnvironment.getAjax().sendGet(href + req, false, null);
	};

	/** Get the position of an element by going up the DOM tree and adding up all the offsets */
	this.getPosition = function (e)
	{
		var left = 0;
		var top = 0;
		/** Safari fix -- thanks to Luis Chato for this! */
		if (e.offsetHeight == 0)
		{
			/** Safari 2 doesn't correctly grab the offsetTop of a table row
			 this is detailed here:
			 http://jacob.peargrove.com/blog/2006/technical/table-row-offsettop-bug-in-safari/
			 the solution is likewise noted there, grab the offset of a table cell in the row - the firstChild.
			 note that firefox will return a text node as a first child, so designing a more thorough
			 solution may need to take that into account, for now this seems to work in firefox, safari, ie */
			e = e.firstChild; // a table cell
		}

		while (e.offsetParent)
		{
			left += e.offsetLeft;
			top += e.offsetTop;
			e = e.offsetParent;
		}

		left += e.offsetLeft;
		top += e.offsetTop;

		return {x: left, y: top};
	};

	/** Get the mouse coordinates from the event (allowing for browser differences) */
	this.mouseCoords = function (ev)
	{
		if (ev.pageX || ev.pageY)
		{
			return {x: ev.pageX, y: ev.pageY};
		}
		return {
			x: ev.clientX + document.body.scrollLeft - document.body.clientLeft,
			y: ev.clientY + document.body.scrollTop - document.body.clientTop
		};
	};

	/** Given a target element and a mouse event, get the mouse offset from that element.
	 To do this we need the element's position and the mouse position */
	this.getMouseOffset = function (target, ev)
	{
		ev = ev || window.event;

		var docPos = this.getPosition(target);
		var mousePos = this.mouseCoords(ev);
		return {x: mousePos.x - docPos.x, y: mousePos.y - docPos.y};
	};

	/** Take an item and add an onmousedown method so that we can make it draggable */
	this.makeDraggable = function (item)
	{
		if (!item)
		{
			return;
		}
		var self = this; // Keep the context of the TableDnd inside the function

		var drags = item.getElementsByClassName('drag');

		if (drags.length) {
			item.dragElement = drags[0];
			item.dragElement.onmousedown = function (ev)
			{
				// Need to check to see if we are an input or not, if we are an input, then
				// return true to allow normal processing
				var target = getEventSource(ev);
				if (target.tagName == 'INPUT' || target.tagName == 'SELECT')
				{
					return true;
				}
				currenttable = self;
				self.dragObject = item;
				self.mouseOffset = self.getMouseOffset(item, ev);
				return false;
			};
			item.dragElement.style.cursor = "move";
		}
	};

	/** We're only worried about the y position really, because we can only move rows up and down */
	this.findDropTargetRow = function (ev)
	{
		var element = getEventSource(ev);

		// Find TR.
		while (element && element.tagName != 'TR') {
			element = element.parentNode;
		}

		if (element) {
			var table = element;

			// Check TABLE only for move in TBODY.
			while (table && table.tagName != 'TABLE') {
				table = table.parentNode;
				if (table.tagName == 'THEAD' || table.tagName == 'TFOOT') {
					return null;
				}
			}

			if (table && table == this.table) {
				return element;
			}
		}

		return null;
	}
}

/**
 * Class GeneralTreePicker
 *
 * Provide methods to handle tree picker.
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
var GeneralTreePicker =
{
	/**
	 * Open a tree selector in a modal window
	 *
	 * @param {object} options An optional options object
	 */
	openModal: function(options) {
		var opt = options || {},
			max = (window.getSize().y-180).toInt(),
			self = this;
		if (!opt.height || opt.height > max) opt.height = max;
		var M = new SimpleModal({
			'width': opt.width,
			'btn_ok': Contao.lang.close,
			'draggable': false,
			'overlayOpacity': .5,
			'onShow': function() { document.body.setStyle('overflow', 'hidden'); },
			'onHide': function() { document.body.setStyle('overflow', 'auto'); }
		});
		M.addButton(Contao.lang.close, 'btn', function() {
			this.hide();
		});
		M.addButton(Contao.lang.apply, 'btn primary', function() {
			var val = [],
				frm = frm = window.frames['simple-modal-iframe'];

			if (frm === undefined) {
				alert('Could not find the SimpleModal frame');
				return;
			}
			if (frm.document.location.href.indexOf('contao/main.php') != -1) {
				alert(Contao.lang.picker);
				return; // see #5704
			}

			var inp = self.getInput(frm.document);
			if (inp == null) {
				alert('Could not find the input fields.');
				return;
			}

			for (var i=0; i<inp.length; i++) {
				if (!inp[i].checked || inp[i].id.match(/^check_all_/)) continue;
				if (!inp[i].id.match(/^reset_/)) val.push(inp[i].get('value'));
			}
			if (opt.tag) {
				$(opt.tag).value = val.join(',');
				opt.self.set('href', opt.self.get('href').replace(/&value=[^&]*/, '&value='+val.join(',')));
			} else {
				var element = $('ctrl_'+opt.id);
				element.value = val.join("\t");

				// TODO: rewrite using GeneralEnvironment.getAjax().sendPost();
				new Request.Contao({
					field: element,
					evalScripts: false,
					onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
					onSuccess: function(txt, json) {
						$('ctrl_'+opt.id).getParent('div').set('html', json.content);
						json.javascript && Browser.exec(json.javascript);
						AjaxRequest.hideBox();
						window.fireEvent('ajax_change');
					}
				}).post({'action':'reloadGeneralTreePicker', 'name':opt.id, 'value':element.value, 'REQUEST_TOKEN':Contao.request_token});
			}
			this.hide();
		});
		M.show({
			'title': opt.title,
			'contents': '<iframe src="' + opt.url + '" name="simple-modal-iframe" width="100%" height="' + opt.height + '" frameborder="0"></iframe>',
			'model': 'modal'
		});
	},

	getInput: function(element){
		if(element.getElementById('tl_listing') != null){
			return element.getElementById('tl_listing').getElementsByTagName('input');
		}

		if(element.getElementById('tl_select') != null){
			return element.getElementById('tl_select').getElementsByTagName('input');
		}

		return null;
	}
};

//*************************************************************************
// Functions
//*************************************************************************

/**
 * Logger wrapper.
 *
 * This class/function add a wrapper for the console. It shoul prevent the
 * system for a exception if no console is set.
 *
 * @constructor
 */
var GeneralLogger =
{
	disableLog: null,
	setDisableLog: function (flag)
	{
		this.disableLog = !!!flag;
	},
	log: function ($msg)
	{
		if (!this.disableLog && (typeof console == "object" || "console" in window))
		{
			try
			{
				console.log($msg);
			} catch (e)
			{
				// Nothing to do, just die.
			}
		}
	},
	info: function ($msg)
	{
		if (!this.disableLog && (typeof console == "object" || "console" in window))
		{
			try
			{
				console.info($msg);
			} catch (e)
			{
				// Nothing to do, just die.
			}
		}
	}
};


/**
 * AJAX class
 *
 * This class provides functions for sending ajax requests as post and get.
 *
 * @constructor
 */
var GeneralAjaxCaller =
{
	logger: null,
	setLogger: function (objLogger)
	{
		this.logger = objLogger;
	},
	sendPost: function (strAdress, arrData, blnasync, callback)
	{
		var xmlhttp;

		// Code for IE7+, Firefox, Chrome, Opera, Safari
		if (window.XMLHttpRequest)
		{
			xmlhttp = new XMLHttpRequest();
		}
		// Ups we can not send a request add a log.
		else
		{
			this.logger.log('Can not find the XMLHttpRequest object. Can not send a request.');
			return;
		}

		// Check if async.
		if (!!!blnasync == true)
		{
			xmlhttp.onreadystatechange = callback;
		}

		// Open.
		xmlhttp.open("POST", strAdress, !!!blnasync);

		// Add the data to the request.
		for (var key in arrData)
		{
			xmlhttp.setRequestHeader(key, arrData[key]);
		}

		// CALL.
		xmlhttp.send();
	},
	sendGet: function (strAdress, blnasync, callback)
	{
		var xmlhttp;

		// Code for IE7+, Firefox, Chrome, Opera, Safari
		if (window.XMLHttpRequest)
		{
			xmlhttp = new XMLHttpRequest();
		}
		// Ups we can not send a request add a log.
		else
		{
			this.logger.log('Can not find the XMLHttpRequest object. Can not send a request.');
			return;
		}

		// Check if async.
		if (!!!blnasync == true)
		{
			xmlhttp.onreadystatechange = callback;
		}

		// Open and send.
		xmlhttp.open("GET", strAdress, !!!blnasync);
		xmlhttp.send();
	}
};

/**
 * General class with dom mainpulation.
 *
 * @type {{getPreviousSibling: getPreviousSibling}}
 */
var GeneralDom =
{
	/**
	 * Search for the previos sibling and ignore whitepaces.
	 *
	 * @param element
	 *
	 * @returns {*}
	 */
	getPreviousSibling: function (element)
	{
		var p = element;

		do {
			p = p.previousSibling;
		}
		while (p && p.nodeType != 1);

		return p;
	},

	/**
	 * Get the base url from the base tag of contao.
	 */
	getContaoBase: function ()
	{
		var baseTag = document.getElementsByTagName('base');

		if (baseTag == null)
		{
			return null;
		}

		return baseTag[0].getAttribute('href');
	},

	/**
	 * Remove all classes from a element.
	 *
	 * @param element
	 */
	removeAllClasses: function (element)
	{
		element.className = "";
	},

	/**
	 * Remove a class.
	 *
	 * @param element
	 * @param cssClass
	 */
	removeClass: function (element, cssClass)
	{
		element.className += " " + cssClass;
	},

	/**
	 * Add a new class.
	 *
	 * @param element
	 * @param cssClass
	 */
	addClass: function (element, cssClass)
	{
		element.className = element.className.replace(/(?:^|\s)cssClass(?!\S)/, '');
	}

};

//*************************************************************************
// Env
//*************************************************************************

var GeneralEnvironment =
{
	// Vars.
	instanceLogger: null,
	instanceAjax: null,
	instanceDom: null,

	// Functions logger.
	setLogger: function (obj)
	{
		this.instanceLogger = obj;
	},
	getLogger: function ()
	{
		return this.instanceLogger;
	},

	// Functions ajax.
	setAjax: function (obj)
	{
		this.instanceAjax = obj;
	},
	getAjax: function ()
	{
		return this.instanceAjax;
	},

	// Functions dom.
	setDom: function (obj)
	{
		this.instanceDom = obj;
	},
	getDom: function ()
	{
		return this.instanceDom;
	}
};

// Init the env.
GeneralEnvironment.setLogger(GeneralLogger);
GeneralEnvironment.setAjax(GeneralAjaxCaller);
GeneralEnvironment.getAjax().setLogger(GeneralEnvironment.getLogger());
GeneralEnvironment.setDom(GeneralDom);

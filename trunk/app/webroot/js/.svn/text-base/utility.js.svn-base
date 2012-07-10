Object.extend(String.prototype, {
	isNumeric: function() {
		//blank strings are always considered valid
		return this == "" || /^-?([0-9]+(\.|(\.[0-9]+){0,1})|\.[0-9]+)$/.test(this);
	},
	
	isDate: function()
	{	
		//blank strings are always considered valid
		if (this == "")
		{
			return true;
		}
		
		//mm/dd/yyyy
		var monthDayYearPattern = /^(1[0-2]|0?[1-9])\/(0?[1-9]|[12][0-9]|3[01])\/(\d\d\d\d)$/;
		var yearMonthDayPattern = /^\d{4}-\d{2}-\d{2}$/;
		
		var dateParts = "";
		var month = "";
		var day = "";
		var year = "";
		
		if (monthDayYearPattern.test(this))
		{
			dateParts = this.split("/");
			month = parseInt(dateParts[0], 10);
			day = parseInt(dateParts[1], 10);
			year = parseInt(dateParts[2], 10);
		}
		else if (yearMonthDayPattern.test(this))
		{
			dateParts = this.split("-");
			month = parseInt(dateParts[1], 10);
			day = parseInt(dateParts[2], 10);
			year = parseInt(dateParts[0], 10);
		}
		else
		{
			return false;
		}

		if (!mrs.isDateValid(month, day, year))
		{
			return false;
		}
		
		return true;
	},
	
	isTime: function() {
		var standard = /^(0?[0-9]|1[12]):([0-5][0-9])(:([0-5][0-9]))?( [AP]M)?$/;
		var military = /^(0?[0-9]|1[0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9]))?$/;

		return standard.test(this) || military.test(this);
	},
	
	toDatabaseDateString: function() {
		if (this.isDate()) 
		{
			return new Date(Date.parse(this)).toDatabaseDateString();
		}
		
		return null;
	}
});

Object.extend(Date.prototype, {
	toShortDateString: function() {
		return (this.getMonth() + 1).toString() + "/" + this.getDate().toString()  + "/" + this.getFullYear().toString();
	},
	
	toShortDateTimeString: function() {
		return new Template("#{date} #{hours}:#{minutes} #{meridiem}").evaluate({
			date: this.toShortDateString(),
			hours: (this.getHours() == 0 || this.getHours() == 12 ? 12 : (this.getHours() % 12)).toPaddedString(2),
			minutes: this.getMinutes().toPaddedString(2),
			meridiem: this.getHours() <= 11 ? "AM" : "PM"
		});
	},
		
	toDatabaseDateString: function() {
		return new Template("#{year}-#{month}-#{day}").evaluate({
			year: this.getFullYear().toString(),
			month: (this.getMonth() + 1).toPaddedString(2),
			day: this.getDate().toPaddedString(2)
		});
	},
	
	toDatabaseDateTimeString: function() {
		return new Template("#{date} #{hours}:#{minutes} #{meridiem}").evaluate({
			date: this.toDatabaseDateString(),
			hours: (this.getHours() == 0 || this.getHours() == 12 ? 12 : (this.getHours() % 12)).toPaddedString(2),
			minutes: this.getMinutes().toPaddedString(2),
			meridiem: this.getHours() <= 11 ? "AM" : "PM"
		});
	}
});

Object.extend(Number.prototype, {
	format: function(decimals) {
		
		_this = Math.round(this * Math.pow(10, decimals)) / Math.pow(10, decimals);
		
		var parts = (_this + "").split(".");
		var x1 = parts[0];
		var x2 = parts.length > 1 ? parts[1] : "";
		var regex = /(\d+)(\d{3})/;

		while (regex.test(x1))
		{
			x1 = x1.replace(regex, "$1,$2");
		}

		var missing = decimals - x2.length;
		return x1 + (decimals > 0 ? "." : "") + x2 + "0".times(missing);
	}
});

Hash.addMethods({
	containsKey: function(key) {
		return this._object.hasOwnProperty(key);
	}
});

//static method
Date.normalize = function(value) {

	//if we get a database date string, we'll normalize it to MM/dd/yyyy,
	//otherwise we'll leave it alone
	if (/^\d{4}-\d{2}-\d{2}( .*)?/.test(value))
	{
		var parts = value.split(" ");
		var date = parts.shift().split("-");
		var time = parts.join(" ");
		
		value = date[1] + "/" + date[2] + "/" + date[0];

		//if we actually had a datetime, throw the time back on
		if (time.isTime())
		{
			value = value + " " + time;
		}
	}

	return value;		
}

//overridding element.scrollTo to take our fixed header into account
Element.addMethods({
	scrollTo: function(element) {
		element = $(element);
		var pos = Element.cumulativeOffset(element);
		window.scrollTo(pos[0], pos[1] - ($("Header").getHeight() + 25));
		return element;
	}
});

var mrs = {	
	fixIEInputs: function(formName) {
		if (Prototype.Browser.IE)
		{
			var formElement = $(formName);
			
			formElement.select("div.Horizontal>input").each(function(formInput) {
				$(formInput).wrap('span');
			});
		}
	},
	
	bindDatePicker: function(elementName) {
		var element = $(elementName);
		
		var options = Object.extend({
			onSelect: Prototype.emptyFunction,
			showTime: false
		}, arguments[1] || {});
		
		var a = Element.extend(document.createElement("a"));
		a.href = "#";
		
		//the vertical-top rule is to prevent FireFox from mis-aligning fields when multiple are floated left
		a.update("<img style=\"vertical-align: top; margin-left: 1px;\" src=\"/img/calendar.png\" />");

		Event.observe(a, "click", function(evt) {
			var existingCalendar = $(element.id + "_cal");
						
			//if we already have the calendar showing, hide it
			if (existingCalendar)
			{
				existingCalendar.remove();
				Event.stop(evt);
				return;
			}
	
			//create the div to hold the calendar
			var container = Element.extend(document.createElement("div"));
			var position = Position.cumulativeOffset(element);
			var dimensions = element.getDimensions();
			
			container.id = element.id + "_cal";
			
			//set up the position of the div
			container.setStyle({
				position: "absolute",
				backgroundColor: "#FFFFFF",
				left: position[0] + "px",
				top: (position[1] + dimensions.height) + "px",
				zIndex: 100
			});
			
			//create the date picker
			var picker = new FastDatePicker();
			picker.showTime = options.showTime;
			
			//wire it up so that when a date is picked, the element we're binding 
			//to gets the date in mm/dd/yyyy format
			picker.handleSelection = function dr_SelectDate(container, picker) {
				this.value = options.showTime ? picker.date.toShortDateTimeString() : picker.date.toShortDateString();
				container.remove();
				options.onSelect(this);
			}.bind(element, container, picker);
			
			//don't put a restriction on the dates they can pick
			picker.firstSelectableDate = null;
			
			//if the field already has a date in it, let's tell the date picker
			//to default to that date
			if (!element.value.blank())
			{
				var parts = element.value.strip().split(" ");
				var date = parts.shift();
				var time = parts.join(" ");

				if (date.isDate() && (parts.length == 0 || time.isTime()))
				{
					picker.date = new Date(Date.normalize(date + (time.blank() ? "" : (" " + time))));
					picker.selectedDate = new Date(picker.date);
				}
			}
			
			//add the picker and container to the document
			container.appendChild(picker.calendar());
			element.up().appendChild(container);
			
			Event.stop(evt);
		});
		
		//add the a tag after the element
		if (!element.nextSibling)
		{
			element.up().appendChild(a);
		}
		else
		{
			element.up().insertBefore(a, element.nextSibling);
		}
		
		//auto-format manually entered dates
		mrs.bindDateFormatting(element);
	},
	
	bindMailto: function() {
		var f = function(event) { document.location.href = "mailto:" + this.value; event.stop() };
		
		$A(arguments).each(function(arg) {
			arg = $(arg);
			var a = new Element("a", { href: "#" }).observe("click", f.bindAsEventListener(arg));
			a.insert(new Element("img", { src: "/img/iconEmail.png" }));
			arg.insert({ after: a });
		});
	},
	
	bindDateFormatting: function() {
		var f = function(event) { 
			var v = $F(event.element());
			
			if (v.match(/^([0-9]{2})([0-9]{2})([0-9]{4})$/))
			{
				event.element().value = RegExp.$1 + "/" + RegExp.$2 + "/" + RegExp.$3;
			}
		};
		
		$A(arguments).each(function(arg) {
			$(arg).observe("blur", f);
		});
	},
	
	bindPhoneFormatting: function() {
		var f = function(event) {
			var v = $F(event.element());
			
			if (v.match(/^([0-9]{3})([0-9]{3})([0-9]{4})$/))
			{
				event.element().value = "(" + RegExp.$1 + ") " + RegExp.$2 + "-" + RegExp.$3;
			}
		};
		
		$A(arguments).each(function(arg) {
			$(arg).observe("blur", f);
		});
	},
	
	bindSSNFormatting: function() {
		var f = function(event) {
			var v = $F(event.element());
			
			if (v.match(/^([0-9]{3})([0-9]{2})([0-9]{4})$/))
			{
				event.element().value = RegExp.$1 + "-" + RegExp.$2 + "-" + RegExp.$3;
			}
		};
		
		$A(arguments).each(function(arg) {
			$(arg).observe("blur", f);
		});
	},
	
	fixAutoCompleter: function(element) {		

		if (Prototype.Browser.IE)
		{
			element = $(element);
			
			//execute the bad code and swallow the error
			try 
			{
				$(element.id + "_autoComplete").clonePosition(element, {
					setHeight: false, 
					offsetTop: element.offsetHeight
				});
			} catch (e) {}
		}
	},
	
	isDateValid: function(month, day, year)
	{
		if (month.toString().blank() 
			|| day.toString().blank() 
			|| year.toString().blank()
			|| !month.toString().isNumeric() 
			|| !day.toString().isNumeric() 
			|| !year.toString().isNumeric() 
			|| month.toString().indexOf(".") != -1
			|| day.toString().indexOf(".") != -1
			|| year.toString().indexOf(".") != -1
			|| year.toString().length != 4)
		{
			return false;
		}
		
		var date = new Date(year, month - 1, day, 0, 0, 0, 1);
		return date.getMonth() + 1 == month && date.getDate() == day && date.getFullYear() == year;
	},
	
	createWindow: function(width, height) {
		
		//adjust the top of where the window gets centered due to our fixed header
		var viewport = document.viewport; //$(document.body);
		var viewportHeight = viewport.getDimensions().height;
		var offset = $("Header").getHeight();
		viewportHeight -= offset;
		var top = ((viewportHeight - height) / 2) + offset + $(document.body).viewportOffset().top;
		
		var win = new UI.Window(Object.extend({
				theme: "mac_os_x",
				width: width,
				height: height,
				minimize: false,
				maximize: false
			}, arguments[2] || {})).center({ top: top });
		
		if (arguments[3])
		{
			win.setContent(arguments[3]);
		}
		
		return win;
	},
	
	_dialog: null,
	
	showDialog: function(message) {
		
		var focusedElement = arguments[1] || null;
		var width = arguments[2] || 450;
		var height = arguments[3] || 100;
		var options = arguments[4] || {};
		var allowDismiss = arguments[5] == undefined ? true : arguments[5];
		var showStyled = arguments[6] == undefined ? true : arguments[6];
		
		//create the window
		var output;
		if (showStyled)
		{
			output = "<h2 style=\"text-align: center; margin: 10px auto;\">" + message + "</h2>";
		}
		else
		{
			output = message;
		}
		
		var win = mrs.createWindow(width, height, options, output).show(true).activate();

		if (allowDismiss)
		{
			//store a global reference for our callbacks
			_dialog = win;
			
			//wire up callbacks to kill the window on keypress
			win.observe("destroyed", mrs._handleDialogDestroyed.bindAsEventListener(win, focusedElement));
			document.observe("keypress", mrs._handleDialogKeypress);
		}
		
		//focus on the close button if we have one
		var close = win.getButtonElement("close");
		
		if (close) 
		{
			close.focus();
			close.blur();
		}
		else
		{
			//if we can't focus on the close button, create a button, focus it, then hide it
			var button = new Element("button");
			win.content.insert(button);
			button.focus();
			button.hide();
		}
		
		return win;
	},
	
	_handleDialogDestroyed: function(event, focusedElement) {
		document.stopObserving("keypress", mrs._handleDialogKeypress);
		
		if (focusedElement)
		{
			focusedElement.focus();
		}
		
		_dialog = null;
	},
	
	_handleDialogKeypress: function(event) {
		_dialog.destroy();
	},
	
	confirmDialog: function(message, okCallback) {
		var win = mrs.createWindow(400, 150, arguments[2] || { close: false }, "<h2 style=\"text-align: center; margin: 10px auto;\">" + message + "</h2><div style=\"text-align: center;\"><button id=\"WindowDialogOK\" class=\"StyledButton\">OK</button><button id=\"WindowDialogCancel\" class=\"StyledButton\">Cancel</button></div>");
		win.show(true).activate();
		
		$("WindowDialogOK").observe("click", mrs._handleConfirmOK.bindAsEventListener(win, okCallback)).focus();
		$("WindowDialogCancel").observe("click", mrs._handleConfirmCancel.bindAsEventListener(win));
		
		return win;
	},
	
	_handleConfirmOK: function(event, callback) {
		this.destroy();
		callback();
	},
	
	_handleConfirmCancel: function(event) {
		this.destroy();
	},
	
	/**
	 * Creates and shows a modal loading dialog. Use Window.destroy() to close the dialog, as the close method
	 * will not work in this case.
	 */ 
	showLoadingDialog: function()
	{
		return mrs.showDialog("Loading. Please wait", null, 300, 100, { close: false }, false);
	},
	
	/**
	 * Finds the number of days in a month. Relies on a trick with JavaScript date constructors where the zero'th day
	 * of the next month would result in a date that is the last day of the month you're looking for.
	 * @param int month The month to look for - this is 1-indexed, not zero.
	 * @param int year The year to look for.
	 * @return int The number of days in the month.
	 */
	daysInMonth: function(month, year) {
		return new Date(year, month, 0).getDate();
	},
	
	/**
	 * Wrapper around the Prototip library to apply default options.
	 * @param object element The element to put the tooltip on.
	 * @param mixed content If using a standard tooltip, this will be the content of the tip. But for an AJAX tooltip,
	 * this is the hash of options for the AJAX (see Prototip doc for details).
	 * @param hash options These are the options for the tooltip (see Prototip doc for details).
	 */
	createTooltip: function(element, content, options) {
		
		if (typeof(content) == "string")
		{
			new Tip(element, content, Object.extend({ style: "darkgrey" }, options || {}));
		}
		else
		{
			new Tip(element, options);
		}
	},
	
	/**
	 * Disables all controls within a given container scope.
	 * @param object scopeElement The element to scope the disabling to. Only child controls of the 
	 * element will be disabled. Pass null to scope it to the entire document.
	 */
	disableControls: function(scopeElement)
	{
		scopeElement = scopeElement || "body";
		
		$(scopeElement).select("input", "textarea", "select").each(function(input) {
			input.addClassName("ReadOnly");
			input.setAttribute("readOnly", "readOnly");
			input.setAttribute("tabIndex", -1);
			
			if (input.type == "checkbox")
			{
				input.observe("click", function(e) { e.stop(); });
			}
			else if (input.tagName.toLowerCase() == "select")
			{
				var span = new Element("div").update(input.options[input.selectedIndex].innerHTML).setStyle({ marginBottom: "5px" });;
				input.insert({ after: span });
				input.hide();
			}
		});
	},
	
	/** Default scrollable table options */
	dataTableOptions: { sScrollY: "200px", bPaginate: false, bInfo: false, bFilter: false },
	
	/** 
	 * Holds a reference to all tables that have been made scrollable. Indexed by table ID. If the 
	 * table doesn't have an ID, it will have a key of "unknown". That means multiple tables made scrollable without IDs will overwrite one another in this list!
	 * So make sure you use IDs in your tables if you plan on using this :) 
	 **/
	tables: {},
	
	/** 
	 * Makes a table scrollable, sortable, etc. using jQuery.dataTables.
	 * There are two ways to call this method:
	 * Method 1 (single table):
	 * 		@param mixed tables The ID of the table or the table element itself to make scrollable.
	 * 		@param (optional) hash options An optional hash of options that are forwarded on to the jQuery.dataTable plugin. If no
	 * 		options are specified, mrs.dataTableOptions are used.
	 *
	 * Method 2 (multiple tables):
	 * 		@param array tables An array of objects, each of which has two keys:
	 * 			table - The ID of the table or the table element itself to make scrollable.
	 * 			options - An optional hash of options that are forwarded on to the jQuery.dataTable plugin. If no options are specified, 
	 * 			mrs.dataTableOptions are used.
	 */
	makeScrollable: function(tables) {
		
		//get the arguments into a unified form regardless of how it was invoked
		if (Object.isArray(tables))
		{
			tables.each(function(t) {
				t.options = Object.extend(Object.clone(mrs.dataTableOptions), t.options || {})
			});
		}
		else
		{
			var table = {
				table: tables,
				options: Object.extend(Object.clone(mrs.dataTableOptions), arguments[1] || {})
			};
			
			tables = [table];
		}
		
		//load jQuery if we don't have it yet and then make the table scrollable
		if (typeof jQuery == "undefined")
		{
			mrs._loadJQueryDataTables(mrs._applyScrollable.curry(tables));
		}
		else
		{
			mrs._applyScrollable(tables);
		}
	},
	
	/**
	 * Internally used to load jQuery and jQuery.dataTables automatically.
	 * @param function callback The function to call when the scripts are done loading.
	 */
	_loadJQueryDataTables: function(callback) {
		
		var head = $$("head")[0];
		
		//load the CSS for the tables
		var style = new Element("link", { rel: "stylesheet", type: "text/css", href: "/css/dataTables.css" });
		head.insert(style);
		
		//create a jQuery script tag
		var jQueryScript = new Element("script", { 
			type: "text/javascript", 
			src: "/js/jquery.js"
		});
		
		//create a jQuery.dataTables script tag
		var dataTablesScript = new Element("script", { 
			type: "text/javascript", 
			src: "/js/jquery.dataTables.js"
		});
	
		//insert the jQuery script
		head.insert(jQueryScript);

		//now we have to wait for the script to finish loading before we can load the dataTables script or else it will fail to load
		new PeriodicalExecuter(function(pe) {
										
			if (typeof jQuery != "undefined")
			{
				//once we have jQuery loaded, load the dataTables script
				pe.stop();
				head.insert(dataTablesScript);
				
				//now wait for that to be done loading before we call the original callback specified to the function that is to be
				//called when both libraries are loaded
				new PeriodicalExecuter(function(pe2) {
					if (jQuery.fn.dataTable)
					{
						pe2.stop();
						callback();
					}
				}, .25);
			}
		}, .25);
	},
	
	/**
	 * Internally used to apply the jQuery.dataTables plugin to a particular HTML table.
	 * @param mixed tables An array of objects containing two keys:
	 * 		table - The ID of the table or the table element itself to make scrollable.
	 * 		options - A hash of options that are forwarded on to the jQuery.dataTable plugin.
	 */
	_applyScrollable: function(tables) {
		$A(tables).each(function(t) {
			var table = $(t.table);
			
			//undo any "alt" row styling of ours since dataTables will take over the striping
			if (table.hasClassName("Styled"))
			{
				table.select("tr").invoke("removeClassName", "Alt");
			}
			
			//make the table scrollable and hang on to the object reference for client code to grab
			mrs.tables[table.id != "" ? table.id : "unknown"] = jQuery(table).dataTable(t.options);
		});
	}
}
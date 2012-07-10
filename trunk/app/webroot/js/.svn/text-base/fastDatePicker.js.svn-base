function FastDatePicker() {
	/* Fast Date Picker 0.02 (http://fastdatepicker.sourceforge.net/)

	Copyright (c) 2005-2006, Jonas Koch Bentzen
	All rights reserved.

	Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

		* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
		* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
		* Neither the name of the product nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	_______________________________________________________________________________________________



	How to Integrate the Date Picker into an XHTML Page
	^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

	1.	In the <head> part of your XHTML file, write something like this:

		<link rel='stylesheet' href='fastDatePicker.css' />
		<script type='text/javascript' src='FastDatePicker.js'></script>

	2.	In the <body>, create an empty <div>:

		<div id='calendarContainer'></div>

	3.	Initialize the date picker object and change some of the default settings if
		necessary. See the list of propeterties that can be changed below. Create the
		function that is to be called when the user selects a date. Finally, insert the
		calendar into the page.

		<script type='text/javascript'>
			// Initializing the date picker object:
			var fastDatePicker = new FastDatePicker()

			// Changing some of the default settings if necessary:
			fastDatePicker.emphasizedDaysOfWeek = [0]
			fastDatePicker.highlightToday = false

			// This function will be called when the user selects a date:
			function handleSelection() {
				// Do something with fastDatePicker.date (the date selected by the user).
				// E.g., you could insert the date into the date selection form fields on
				// your page.
			}

			// Showing the calendar:
			document.getElementById('calendarContainer').appendChild(fastDatePicker.calendar())
		</script>
	*/



	// Default settings (see the above example on how to change them):

	/* The name of your own function or method which is called whenever a user selects a
	date. The name should not be quoted or followed by "()". */
	
	//BN: modified for prototype
	this.handleSelection = Prototype.EmptyFunction;

	/* Set this to true if you want the first day displayed to be Sunday. Set it to false
	if you want the first day displayed to be Monday. */
	this.weekStartsWithSunday = true

	/* If you want emphasize some of the week days (e.g. Saturday and Sunday), write the
	number of those days here as an array literal. 0 = Sunday, 1 = Monday, etc. */
	this.emphasizedDaysOfWeek = [0, 6]

	/* Set this to true or false depending on whether you want the current date to be
	highlighted. */
	this.highlightToday = true
	
	//BN: added support for highlighting the selected date
	this.selectedDate = null;
	this.highlightSelected = true;
	
	//BN: added support for time
	this.showTime = false;

	/* By default, the calendar will open with the month and year contained in this Date
	object. */
	this.date = new Date()

	/* If you want the users to be able to select all dates (even dates in the past), set
	this to null. If the users should only be allowed to select dates from a certain date
	onwards, set this to a Date object representing the first selectable date. This date
	and all future dates will then be selectable. */
	this.firstSelectableDate = new Date()

	// The names (or abbreviations) of the months in your language.
	this.monthNames = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')

	/* The names (or abbreviations) of the days of week in your language. Start with
	Sunday - even if you have set this.weekStartsWithSunday to false. */
	this.daysOfWeek = new Array('S', 'M', 'T', 'W', 'T', 'F', 'S')



	// Private variables - don't change these:

	this.CSSSelectorPrefix = 'fastDatePicker'
	this.tableBody = document.createElement('tbody')
	this.cellYearMonth



	this.isLeapYear = function(year) {
		return (year % 4 == 0 && !(year % 100 == 0 && year % 400 != 0))
	}



	this.numDaysInMonth = function() {
		switch (this.date.getMonth()) {
			case 1: // February
				return (this.isLeapYear(this.date.getFullYear())) ? 29 : 28
			case 3: // April
			case 5: // June
			case 8: // September
			case 10: // November
				return 30
			default:
				return 31
		}
	}



	this.deleteDays = function() {
		for (var i = 7; i >= 2; i--) {
			this.tableBody.removeChild(this.tableBody.lastChild)
		}
	}



	this.previousMonth = function(event) {
		var calendarObject = (event) ? event.target.calendarObject : window.event.srcElement.calendarObject
		var currentMonth = calendarObject.date.getMonth()

		calendarObject.date.setDate(1)

		if (currentMonth == 0) {
			calendarObject.date.setFullYear(calendarObject.date.getFullYear() - 1, 11)
		}
		else {
			calendarObject.date.setMonth(currentMonth - 1)
		}

		calendarObject.deleteDays()
		calendarObject.renderDays()
	}



	this.nextMonth = function(event) {
		var calendarObject = (event) ? event.target.calendarObject : window.event.srcElement.calendarObject
		var currentMonth = calendarObject.date.getMonth()

		calendarObject.date.setDate(1)

		if (currentMonth == 11) {
			calendarObject.date.setFullYear(calendarObject.date.getFullYear() + 1, 0)
		}
		else {
			calendarObject.date.setMonth(currentMonth + 1)
		}

		calendarObject.deleteDays()
		calendarObject.renderDays()
	}
	
	//BN - adding support for previous/next year
	this.previousYear = function(event) {
		var calendarObject = (event) ? event.target.calendarObject : window.event.srcElement.calendarObject;
		var currentMonth = calendarObject.date.getMonth();

		calendarObject.date.setFullYear(calendarObject.date.getFullYear() - 1, currentMonth, 1);
		
		calendarObject.deleteDays();
		calendarObject.renderDays();
	}
	
	this.nextYear = function(event) {
		var calendarObject = (event) ? event.target.calendarObject : window.event.srcElement.calendarObject;
		var currentMonth = calendarObject.date.getMonth();

		calendarObject.date.setFullYear(calendarObject.date.getFullYear() + 1, currentMonth, 1);
		
		calendarObject.deleteDays();
		calendarObject.renderDays();		
	}

	this.selectDay = function(event) {
		var callingElement = (event) ? event.target : window.event.srcElement

		callingElement.calendarObject.date.setDate(callingElement.firstChild.nodeValue)

		callingElement.calendarObject.handleSelection()
	}

	//BN: modified to render the head with previous/next year arrows - also the header row is now
	//its own table so that it doesn't dictate the size of the days
	this.renderHead = function() {
		var table, tableRow, tableBody, row, cell, key

		// Month selection row:

		row = document.createElement('tr')
		row.id = this.CSSSelectorPrefix+'RowYearMonth'
		
		//BN - adding a single cell containing another table
		//for the header
		table = document.createElement('table');
		table.id = this.CSSSelectorPrefix+'TableYearMonth';
		
		tableBody = document.createElement('tbody');
		tableRow = document.createElement('tr');
		
		cell = document.createElement('td');
		cell.colSpan = 7;
		
		tableBody.appendChild(tableRow);
		table.appendChild(tableBody);
		cell.appendChild(table);
		row.appendChild(cell);
		
		//BN - previous year
		cell = document.createElement('td')
		cell.className = this.CSSSelectorPrefix+'SelectableElement'
		cell.calendarObject = this
		
		try {
			cell.addEventListener('click', this.previousYear, false)
		}
		catch (exception) {
			cell.onclick = this.previousYear
		}
		cell.appendChild(document.createTextNode('<<'))
		tableRow.appendChild(cell)
		//---

		cell = document.createElement('td')
		cell.className = this.CSSSelectorPrefix+'SelectableElement'
		cell.calendarObject = this
		
		try {
			cell.addEventListener('click', this.previousMonth, false)
		}
		catch (exception) {
			cell.onclick = this.previousMonth
		}
		cell.appendChild(document.createTextNode('<'))
		tableRow.appendChild(cell)

		this.cellYearMonth = document.createElement('td')
		this.cellYearMonth.id = this.CSSSelectorPrefix+'CellYearMonth'
		this.cellYearMonth.colSpan = 3
		this.cellYearMonth.appendChild(document.createTextNode(''))
		tableRow.appendChild(this.cellYearMonth)

		cell = document.createElement('td')
		cell.className = this.CSSSelectorPrefix+'SelectableElement'
		cell.calendarObject = this
		try {
			cell.addEventListener('click', this.nextMonth, false)
		}
		catch (exception) {
			cell.onclick = this.nextMonth
		}
		cell.appendChild(document.createTextNode('>'))
		tableRow.appendChild(cell)
		
		//BN - next year
		cell = document.createElement('td')
		cell.className = this.CSSSelectorPrefix+'SelectableElement'
		cell.calendarObject = this
		
		try {
			cell.addEventListener('click', this.nextYear, false)
		}
		catch (exception) {
			cell.onclick = this.nextYear
		}
		cell.appendChild(document.createTextNode('>>'))
		tableRow.appendChild(cell)
		//---

		this.tableBody.appendChild(row)
		
		//BN: adding support for hover styles in IE6
		this.applyIE6SelectableStyle(table);
		
		// Days of the week:

		row = document.createElement('tr')
		row.id = this.CSSSelectorPrefix+'RowDaysOfWeek'

		for (var i = 0; i < 7; i++) {
			if (this.weekStartsWithSunday) {
				key = i
			}
			else {
				key = (i == 6) ? 0 : i + 1
			}

			cell = document.createElement('td')
			for (var j = 0; j < this.emphasizedDaysOfWeek.length; j++) {
				if (this.emphasizedDaysOfWeek[j] == key) {
					cell.className = this.CSSSelectorPrefix+'EmphasizedDaysOfWeek'
				}
			}
			cell.appendChild(document.createTextNode(this.daysOfWeek[key]))
			row.appendChild(cell)
		}

		this.tableBody.appendChild(row)
	}



	this.renderDays = function() {
		var row, cell
		var numDaysInMonth = this.numDaysInMonth()
		var dayCounter = 1

		this.cellYearMonth.firstChild.nodeValue = this.monthNames[this.date.getMonth()]+' '+this.date.getFullYear()

		var start = this.date.getDay()
		if (!this.weekStartsWithSunday) start = (start == 0) ? 6 : start - 1

		if (this.highlightToday) {
			var date = new Date()

			if (this.date.getFullYear() == date.getFullYear() && this.date.getMonth() == date.getMonth()) {
				var today = date.getDate()
			}
		}
		
		//BN: added support for highlighting the selected date
		if (this.highlightSelected && this.selectedDate) {
			if (this.date.getFullYear() == this.selectedDate.getFullYear() && this.date.getMonth() == this.selectedDate.getMonth()) {
				var selected = this.selectedDate.getDate();
			}
		}

		for (var i = 0; i < 42; i++) {
			if (i % 7 == 0) row = document.createElement('tr')

			cell = document.createElement('td')

			if (i >= start && dayCounter <= numDaysInMonth) {
				this.date.setDate(dayCounter)

				if (today && dayCounter == today) cell.id = this.CSSSelectorPrefix+'CellToday'
				
				//BN: added support for highlighting the selected date
				if (selected && dayCounter == selected) cell.id = this.CSSSelectorPrefix+'CellSelected'

				cell.appendChild(document.createTextNode(dayCounter))

				if (!this.firstSelectableDate || this.date.getTime() >= this.firstSelectableDate) {
					cell.className = this.CSSSelectorPrefix+'SelectableElement'
					cell.calendarObject = this
					try {
						cell.addEventListener('click', this.selectDay, false)
					}
					catch (exception) {
						cell.onclick = this.selectDay
					}
				}
				else {
					cell.className = this.CSSSelectorPrefix+'NonSelectableElement'
				}

				dayCounter++
			}
			else {
				/* Adding a non-breaking space in order to make sure that the
				cell is as high as those cells that have content: */
				cell.appendChild(document.createTextNode(String.fromCharCode(160)))
			}

			row.appendChild(cell)
			if (i % 7 == 0) this.tableBody.appendChild(row)
			
			//BN: adding support for hover styles in IE6
			this.applyIE6SelectableStyle(row);
		}
	}
	
	//BN: support for time
	this.renderTime = function() {

		var cal = this;
		var hours = new Element("select");
		var minutes = new Element("select");
		var meridiem = new Element("select");
		
		//create the meridiem options (we do this first because the selected hour
		//depends on it)
		meridiem.insert(new Element("option", { value: "0", selected: cal.date.getHours() <= 11 }).update("AM"));
		meridiem.insert(new Element("option", { value: "12", selected: cal.date.getHours() > 11 }).update("PM"));
		
		//create the hours options
		(12).times(function(i) {
			var value = i == 11 ? 0 : (i + 1);
			var selected = cal.date.getHours() == value + parseInt($F(meridiem), 10);
			
			hours.insert(new Element("option", { value: value, selected: selected }).update((i + 1).toPaddedString(2)));
		});
		
		//create the minutes options
		(60).times(function(i) {
			minutes.insert(new Element("option", { value: i, selected: cal.date.getMinutes() == i }).update(i.toPaddedString(2)));
		});
		
		//wire up the fields
		hours.observe("change", function() { 
			cal.date.setHours(parseInt($F(this), 10) + parseInt($F(meridiem), 10));
		});
		
		minutes.observe("change", function() {
			cal.date.setMinutes(parseInt($F(this), 10));
		});
		
		meridiem.observe("change", function() {
			cal.date.setHours(parseInt($F(hours), 10) + parseInt($F(this), 10));
		});

		row = new Element("tr").insert(
			new Element("td", { colspan: 7 })
				.insert(hours)
				.insert(minutes)
				.insert(meridiem)
		);
		
		this.tableBody.appendChild(row);
	}

	//BN: adding support for hover styles in IE6
	this.applyIE6SelectableStyle = function(element) {	
		if (!window.XMLHttpRequest) {
			var className = this.CSSSelectorPrefix;
			
			Element.extend(element).getElementsByClassName(this.CSSSelectorPrefix+'SelectableElement').each(function(child) { 
				Event.observe(child, 'mouseover', function(evt) { 
					Event.element(evt).addClassName(className+'SelectableElementHover'); 
				});
				
				Event.observe(child, 'mouseout', function(evt) { 
					Event.element(evt).removeClassName(className+'SelectableElementHover'); 
				});
			});
		}
	}


	this.calendar = function() {
		this.date.setDate(1)

		if (this.firstSelectableDate) {
			this.firstSelectableDate.setHours(0, 0, 0, 0)
			this.firstSelectableDate = this.firstSelectableDate.getTime()
		}

		var table = document.createElement('table')
		table.id = this.CSSSelectorPrefix+'Table'

		this.renderHead()

		this.renderDays()
		
		//BN: support for time
		if (this.showTime)
		{
			this.renderTime();
		}

		table.appendChild(this.tableBody)

		return table
	}
}

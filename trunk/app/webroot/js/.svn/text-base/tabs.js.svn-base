var Tabs = {
	tabs: null,
	pages: null,
	CTRL_KEY: 17,
	ctrlPressed: false,
	selected: null,
	changeCallback: null,
	
	/** 
	 * Called by our own dom:loaded script to apply tab functionality.
	 * Can pass a function as a callback that fires when a tab is changed that accepts one 
	 * argument - the tab page element that was changed to.
	 */
	apply: function() {
		Tabs.pages = $$(".TabPage");
		Tabs.tabs = $$(".TabStrip li");
		Tabs.CTRL_KEY = 17;
		Tabs.ctrlPressed = false;
		Tabs.selected = 0;
		Tabs.changeCallback = arguments[0] || null;

		//find the initially selected tab
		Tabs.tabs.each(function(tab, i) {
			if (tab.hasClassName("Selected"))
			{
				Tabs.selected = i;
			}
		});

		//wire up the tabs so you can click to change them
		Tabs.tabs.each(function(tab, i) {
			tab.down("a").observe("click", Tabs._changeTab.bindAsEventListener(tab, i));
		});
		
		//wire up keypresses for Ctrl+right and Ctrl-left to move between tabs
		document.observe("keydown", function(event) {
			if (event.keyCode == Tabs.CTRL_KEY) {
				Tabs.ctrlPressed = true;
				event.stop();
			}
			else if (Tabs.ctrlPressed && event.keyCode == Event.KEY_RIGHT) {
				Tabs.tabs.each(function(tab, i) {
					if (tab.hasClassName("Selected")) {
						var newTab = i == Tabs.tabs.length - 1 ? Tabs.tabs[0] : tab.next();
						var index = i == Tabs.tabs.length - 1 ? 0 : i + 1;
						
						Tabs._changeTab.bind(newTab)(event, index);
						throw $break;
					}
				});
			}
			else if (Tabs.ctrlPressed && event.keyCode == Event.KEY_LEFT) {
				Tabs.tabs.each(function(tab, i) {
					if (tab.hasClassName("Selected")) {
						var newTab = i == 0 ? Tabs.tabs[Tabs.tabs.length - 1] : tab.previous();
						var index = i == 0 ? Tabs.tabs.length - 1 : i - 1;
						
						Tabs._changeTab.bind(newTab)(event, index);
						throw $break;
					}
				});
			}
		});
		
		document.observe("keyup", function(event) {
			if (event.keyCode == Tabs.CTRL_KEY) {
				Tabs.ctrlPressed = false;
				event.stop();
			}
		});
	},
	
	/** 
	 * Private method to change tabs.
	 * @param Event event The event triggering the change, if any.
	 * @param int index The index of the tab to change to.
	 */
	_changeTab: function(event, index) {
		
		//change tabs
		Tabs.tabs.invoke("removeClassName", "Selected");
		Tabs.tabs[index].addClassName("Selected");
		Tabs.pages.invoke("hide");
		Tabs.pages[index].show();
		
		Tabs.selected = index;
	
		Tabs.tabs[index].down("a").blur();
		
		if (Tabs.changeCallback)
		{
			Tabs.changeCallback(Tabs.pages[index]);
		}
		
		if (event != null)
		{
			event.stop();
		}
	},
	
	/**
	 * Public method to change tabs
	 * @param int index The index of the tab to change to, zero-based.
	 */
	select: function(index) {
		Tabs._changeTab.bind(Tabs.tabs[index])(null, index);
	}					
};

document.observe("dom:loaded", function() {
	Tabs.apply();
});

var Navigation = Class.create({
							  
	/** 
	 * Constructor.
	 * @param object element The element to use to toggle the menu.
	*/
	initialize: function(element) {
		
		//grab the element and bind it to the toggle method to trigger the nav menu on and off
		this.element = $(element);
		this.element.observe("click", this.toggle.bindAsEventListener(this, false));
		this.element.observe("blur", function(event) { 
			if (this.tree.container.visible()) { 
				setTimeout(function() { this.toggle(event, true); }.bind(this), 500);
				
			} 
		}.bind(this));
		
		//create our root tree node and add its container (ul) to the nav
		this.tree = this.node("", 0);
		this.tree.container.addClassName("NavigationTree");
		
		var div = new Element("div").insert(this.tree.container);
		this.element.insert({after: div});
		
		//go grab the top level application folders
		this.request(null);
		this.tree.filled = true;
	},
	
	/** 
	 * Toggles the navigation menu on and off.
	*/
	toggle: function(event, bubble) {
		this.tree.container.toggle();
		event.element().blur();
		
		if (!bubble)
		{
			event.stop();
		}
	},
	
	/** 
	 * Shows a sub-menu at the given place in the hierarchy.
	 * @param array chain An array of application folder IDs.
	*/
	show: function(chain) {

		var node = this.find(chain);
		
		if (!node.filled)
		{
			this.request(chain);
			node.filled = true;
		}

		node.container.show();
	},
	
	/** 
	 * Hides a sub-menu at the given place in the hierarchy.
	 * @param array chain An array of application folder IDs.
	*/
	hide: function(chain) {
		this.find(chain).container.hide();
	},
	
	/** 
	 * Goes out and fetches the menu for the given chain and adds it to the navigation menu.
	 * @param array chain An array of application folder IDs.
	*/
	request: function(chain) {
		new Ajax.Request("/json/navigation/tree/" + (chain != null ? chain[chain.length - 1] : ""), {
			onComplete: function(transport, json) {
				this.add(chain, transport.responseText.evalJSON());
			}.bind(this)
		});
	},
	
	/** 
	 * Used by the request method as its AJAX callback to add a navigation menu.
	 * @param array chain An array of application folder IDs.
	 * @param hash json The json from the AJAX response.
	*/
	add: function(chain, json) {

		var parentContainer = this.tree.container;
		var current = this.tree;
		
		//chain is null if we're at the root
		if (chain == null)
		{
			chain = [];
		}

		//walk down the tree to find the proper place to insert the new menu
		$A(chain).each(function(id) {
			if (child = current.children.get(id))
			{
				parentContainer = current.container;
				current = child;
				return;
			}
			else
			{	
				throw $break;
			}
		});

		//the child nodes of the json response are all of the new menu items that need inserted
		$A(json.children).each(function(child, i) {

			//create a new node, set up its own sub-menu container, and add it the current nav item's children
			var node = this.node(child.ApplicationFolder.folder_name, i);
			node.container.setStyle({ top: "0px", left: (parentContainer.getWidth() - 3) + "px" });
			current.children.set(child.ApplicationFolder.id, node); 
			
			//add an LI item for this menu item and add it to the DOM
			chain.push(child.ApplicationFolder.id);

			current.container.insert(new Element("li")
				.setStyle({position: "relative"})
				.insert(new Element("a", { href: "/navigation/landing/" + encodeURIComponent(child.ApplicationFolder.folder_name.gsub(/ /, "")) })
					.insert(new Element("img", { src: "/img/iconFolder.png" }))
					.insert(child.ApplicationFolder.folder_name)
					.setStyle({ position: "relative", display: "block", width: "100%" })
					.insert((child["0"].subfolder_count > 0 || child["0"].app_count > 0) ? new Element("img", { src: "/img/iconArrowRight.png" }).setStyle({ position: "absolute", right: "0" }) : "")
				)
				.observe("mouseover", this.show.curry(chain.clone()).bind(this))
				.observe("mouseout", this.hide.curry(chain.clone()).bind(this))
			);
			chain.splice(chain.length - 1, 1);
		}.bind(this));
		
		//add an LI item for each application at this level
		$A(json.applications).each(function(app) {
			current.container.insert(new Element("li")
				.setStyle({position: "relative"})
				.insert(new Element("a", { href: app.Application.url, target: app.Application.open_in_new_window == "1" ? "_blank" : "" })
					.setStyle({ display: "block", width: "100%" })
					.update("<img src=\"/img/iconApplication.png\" /> " + app.Application.name)
					.observe("click", this.toggle.bindAsEventListener(this, true))
				)
			);
		}.bind(this));

		//only add the new container if it's not the root one (since it's already added in the constructor)
		//and only if it actually has menu items
		if (parentContainer != current.container && current.container.select("li").length > 0)
		{
			var menu = parentContainer.childElements();
			menu[current.position].insert(current.container);
		}
	},
	
	/** 
	 * Finds the node at the given location in the navigation tree.
	 * @param array chain An array of application folder IDs.
	 * @return hash The node at the given location, or null if not found.
	*/
	find: function(chain) {

		var current = this.tree;
		
		//walk the tree to find the proper node
		if (chain != null)
		{	
			var found = $A(chain).all(function(id) {
				if (child = current.children.get(id))
				{
					current = child;
					return true;
				}

				return false;
			});

			if (!found)
			{
				current = null;
			}
		}
		
		return current;
	},
	
	/** 
	 * Creates a new node for use in the navigation tree.
	 * @param string name The display name of the node.
	 * @param int position The position of the node in its parent container.
	*/
	node: function(name, position) {
		return { name: name, position: position, children: $H(), container: new Element("ul").setStyle({position: "absolute"}).hide(), filled: false };
	}
});
var Modules = {
	_stack: $A(),
	
	/**
	 * Snatched from scriptaculous to load a js script dynamically
	 */
	require: function(module) {
		try
		{
			document.write('<script type="text/javascript" src="' + module + '"><\/script>');
		} 
		catch(e) 
		{
			var script = document.createElement('script');
			script.type = 'text/javascript';
			script.src = module;
			document.getElementsByTagName('head')[0].appendChild(script);
		}
	},
  
	/**
	 * Snatched and modified from scriptaculous to load modules. Automatically creates "namespaces"
	 * for each module that is loaded. To use, include modules.js as:
	 *
	 * <script scr="modules.js?load=Namespace.module_name"></script>
	 *
	 * This would cause there to be a Modules.Namespace object. The script that would be loaded would be
	 * here: /modules/Namespace/module_name.js.
	 * In your module script, you'll want to assume the namespace is already there and add your module to it
	 * like so:
	 * Modules.Namespace.ModuleName = { ... }
	 * 
	 */
	load: function() {
		var js = /modules\.js(\?.*)?$/;
		$$('head script[src]').findAll(function(s) {
			return s.src.match(js);
		}).each(function(s) {
			var includes = s.src.match(/\?.*load=([a-z0-9,._]*)/);
			
			if (includes)
			{
				includes[1].split(',').each(function(include) {
					var parts = include.split(".");
					var namespace = $A(parts[0].split("_")).invoke('capitalize').join("");
					
					if (!Modules[namespace])
					{
						Modules[namespace] = {};
					}

					Modules.require("/js/modules/" + parts[0].toLowerCase() + "/" + parts[1].toLowerCase() + '.js');
				});
			}
		});
	}
};

Modules.load();
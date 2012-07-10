var Tooltips = {
	tooltips: null,
	
	/** 
	 * Called by our own dom:loaded script to apply tooltip functionality.
	 */
	apply: function() {
		Tooltips.tooltips = $$(".Tooltip");
		
		// Wire up the tooltips so you hovering over the container will display them
		Tooltips.tooltips.each(function(tooltip, i) {
			if (tooltip._tooltipApplied)
			{
				throw $continue;
			}
			
			tooltip._tooltipApplied = true;
			
			// Specify tooltip container, add style and find dimensions
			container = tooltip.up();
			container.addClassName('TooltipContainer');
			var containerSize = container.getDimensions();
			
			// Set the position of the tooltip based on the position of the container
			tooltip.setStyle({
				top: containerSize.height + "px",
				left: containerSize.width + "px"
			});
			
			// Show tooltip on mouseover
			container.observe("mouseover", function() {
				tooltip.setStyle({
					display: 'block'
				});
			});
			
			// Hide tooltip on mouseout
			container.observe("mouseout", function() {
				tooltip.setStyle({
					display: 'none'
				});
			});
			
		});
	}
};

document.observe("dom:loaded", function() {
	Tooltips.apply();
});

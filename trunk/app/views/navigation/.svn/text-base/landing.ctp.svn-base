<?php
	$folderRoot = 'navigation/folders/';
	$appRoot = 'navigation/applications/';
	
	echo '<div class="LandingTitle">';
	echo $html->image($folderRoot . ($folder['ApplicationFolder']['landing_page_image'] == null ? 'default.png' : $folder['ApplicationFolder']['landing_page_image']));
	if ($folder['ApplicationFolder']['landing_page_image'] == null)
	{
		echo $html->tag('h1', h($folder['ApplicationFolder']['folder_name']));
	}
	echo '</div>';
	
	if (count($subfolders) > 0)
	{
		echo '<h2>Subdirectories</h2>';
		
		foreach ($subfolders as $subfolder)
		{
			echo '<div class="LandingItem">';
			echo $html->link(
				$html->image($folderRoot . ($subfolder['ApplicationFolder']['thumbnail_image'] == null ? 'default.png' : $application['ApplicationFolder']['thumbnail_image'])) . '<br />' . h($subfolder['ApplicationFolder']['folder_name']), 
				'/navigation/landing/' . urlencode(str_replace(' ', '', $subfolder['ApplicationFolder']['folder_name'])),
				array('escape' => false)
			);
			echo '</div>';
		}
	}
	
	echo '<br style="clear: left;" />';

	if (count($applications) > 0)
	{
		echo '<h2>Applications</h2>';
		
		foreach ($applications as $application)
		{
			echo '<div class="LandingItem">';
			echo $html->link(
				$html->image($appRoot . ($application['Application']['thumbnail_image'] == null ? 'default.png' : $application['Application']['thumbnail_image'])) . '<br />' . h($application['Application']['name']), 
				$application['Application']['url'],
				array('escape' => false)
			);
			echo '</div>';
		}
	}
?>
<?php
	if (count($images) == 0)
	{
		echo '<p>The selected document has no images.</p>';
	}
	else
	{
		echo '<br /><br />';
		
		foreach ($images as $image)
		{
			echo '<div style="position: relative;">';
			
			echo $html->link(
				$html->image('/images/thumbnail/' . $image['Image']['ImageID']), 
				'/images/get/' . $image['Image']['ImageID'], 
				array('escape' => false, 'target' => '_blank')
			);
			
			echo '<p>Page ' . $image['Image']['PageNumber'] . '</p>';
			
			echo '</div>';
		}
	}
?>
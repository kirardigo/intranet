<div style="padding: 10px;">
	<h1><?= $this->data['Process']['name'] ?></h1>
	<table class="Styled">
		<tr>
			<th class="Text25">&nbsp;</th>
			<th>Name</th>
			<th class="Center Text100">Type</th>
			<th class="Right">Size</th>
		</tr>
	<?php
		foreach ($this->data['ProcessFile'] as $file)
		{
			switch($file['mime_type'])
			{
				case 'application/pdf':
					$image = 'iconPdf.png';
					break;
				default:
					$image = 'iconDocument.png';
					break;
			}
			
			$fileinfo = pathinfo($file['filename']);
			
			echo $html->tableCells(
				array(
					$html->image($image, array('title' => $file['filename'])),
					$html->link($file['name'], "/process_files/get/{$file['id']}"),
					array(strtolower($fileinfo['extension']), array('class' => 'Center')),
					array($number->toReadableSize($file['ProcessFile'][0]['size']), array('class' => 'Right'))
				),
				array(),
				array('class' => 'Alt')
			);
		}
	?>
	</table>
</div>
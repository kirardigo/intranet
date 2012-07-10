<div id="DocPopSidebar">
	<div id="DocPopDocuments" class="GroupBox">
		<h2>Documents</h2>
		
		<div class="Content">
			<table id="DocPopDocumentTable" class="Styled">
				<thead>
					<tr><th>Invoice</th><th>TCN</th><th>Doc ID</th></tr>
				</thead>
				<tbody>
					<?php
						foreach ($documents as $document)
						{
							$invoice = $document['Document']['TextField7'] != '' ? $document['Document']['TextField7'] : '[unknown]';
							$tcn = $document['Document']['TextField2'] != '' ? $document['Document']['TextField2'] : '[unknown]';
							
							echo $html->tableCells(
								array(
									$html->link($invoice, '#', array('id' => 'DocPopDocument_' . $document['Document']['DocID'])),
									h($tcn),
									h($document['Document']['DocID'])
								),
								array(),
								array('class' => 'Alt')
							);
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
	
	<div id="DocPopIndexInformation" class="GroupBox">
		<h2>Index Information</h2>
		
		<div class="ScrolledContent"></div>
		<div id="DocPopZoom"></div>
	</div>
</div>

<div id="DocPopCanvas" class="GroupBox">
	<h2>Images</h2>

	<div class="ScrolledContent"></div>
</div>

<br style="clear: left;" />

<script type="text/javascript">
	Modules.Documents.ForCustomer.initialize();
</script>
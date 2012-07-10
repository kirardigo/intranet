
<div class="GroupBox">
	<h2>HCPC ICD9 Crosswalk</h2>
	<div class="Content">
		<div style="margin-bottom: 5px;">
			<?php
				echo $form->input('Code', array(
					'value' => $code,
					'type' => 'text',
					'readonly' => true
				));
			?>	
			
			<?php 
				echo $form->input("Diagnosis.search", array(
						'class' => 'Text250',
						'autocomplete' => 'off',
						'type' => 'text',
						'label' => 'Search Diagnosis',
						'div' => false
				));
				echo '<div style="display: none;" id="Diagnosis_autoComplete" class="auto_complete"></div>';
				
				echo $form->hidden('Diagnosis.code');
				
				echo $html->link($html->image('iconAdd.png', array('style' => 'margin-top: -0px; margin-left: 15px;')), '#', array('escape' => false, 'id' => 'Icd9AddLink'));
			?>
									
			<div class="ClearBoth"></div>
		</div>
		
		<table id="Icd9Table" class="Styled" style="width: 300px;<?php if (count($icd9Records) == 0): ?> display: none;<?php endif; ?>" >
			<tr>
				<th>ICD9 Crosswalk</th>
				<th style="width: 50px;"></th>
			</tr>
			<?php
				foreach ($icd9Records as $row)
				{
					echo $html->tableCells(
						array(
							$form->hidden('icd9CrosswalkID', array('value' => $row['HcpcIcd9Crosswalk']['id'])) .
							h($row['HcpcIcd9Crosswalk']['icd9_code']),
							$html->link($html->image('iconDelete.png'), '#', array('escape' => false, 'class' => 'HcpcIcd9DeleteLink'))
						),
						array('class' => 'Auto'),
						array('class' => 'Alt Auto')
					);
				}
			?>
		</table>
	</div>
</div>
<script type="text/javascript">
	Modules.Hcpc.Icd9.init();
</script>
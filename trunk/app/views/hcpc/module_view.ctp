<?php
	//just a fake form to get CSS rules to work 
	echo $form->create('', array('id' => 'HcpcForm', 'url' => "/hcpc/fakeSave"));
?>

<div class="GroupBox">
	<h2>HCPC Detail</h2>
	<div class="Content">
		<div class="FormColumn">
			<?php
				echo $form->input('Hcpc.code', array(
			'class' => 'Text100'
			));
			echo $form->input('Hcpc.description', array(
				'class' => 'Text300'
			));
			echo $form->input('Hcpc.is_active', array(
				'label' => array('class' => 'Checkbox'),
				'div' => array('style' => 'margin: 5px 0')
			));
			echo $form->input('Hcpc.is_serialized', array(
				'label' => array('class' => 'Checkbox'),
				'div' => array('style' => 'margin: 5px 0')
			));
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('Hcpc.6_point_classification', array(
					'class' => 'Text100',
					'label' => 'Class'
				));
				echo $form->input('Hcpc.pmd_class', array(
					'label' => 'PMD Class'
				));
				
				echo $form->input('Hcpc.cmn_code', array(
					'label' => 'CMN Code',
					'class' => 'Text300'
				));
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('Hcpc.initial_date', array(
					'class' => 'Text100',
					'type' => 'text'
				));
				echo $form->input('Hcpc.discontinued_date', array(
					'class' => 'Text100',
					'type' => 'text'
				));
			?>
		</div>
		<br style="clear: both;" /><br />
	</div>
</div>

<div class="GroupBox">
	<h2>ICD9 Crosswalk</h2>
	<div class="Content">
		<table style="width: 300px;">
			<?php
				foreach ($icd9Records as $i => $row)
				{
					echo $html->tableCells(
						array(array(
							array(($i + 1) . '.', array('style' => 'width: 20px;')),
							h($row['HcpcIcd9Crosswalk']['icd9_code'])
						)),
						array('class' => 'Auto'),
						array('class' => 'Alt Auto')
					);
				}
			?>
		</table>
	</div>
</div>

<?php
	echo $form->end();
?>

<div id="CarrierContent"></div>

<script type="text/javascript">
	//make the form look readonly
	mrs.disableControls("HcpcForm");
	
	//load the hcpc carrier module in readonly mode
	new Ajax.Updater(
		$("CarrierContent").update(), 
		"/modules/hcpc/hcpc_carriers/<?= h($this->data['Hcpc']['code']) ?>/readonly:1/showcode:0",
		{
			evalScripts: true
		}
	);
</script>
<div class="GroupBox" id="HcpcCarrierDetail">
	<h2>HCPC Carrier Detail</h2>
	<div class="Content">
		<?php
			echo $form->create('', array('url' => "/hcpcCarrier/fakeSave", 'id' => 'HcpcCarrierForm'));
			
			echo $form->hidden('HcpcCarrier.id');
			echo $form->input('HcpcCarrier.hcpc_code', array(
				'label' => 'HCPC Code',
				'class' => 'Text100',
				'readonly' => 'readonly',
				'class' => 'ReadOnly',
				'after' => $this->data['Hcpc']['description']
			));
			echo $form->input('HcpcCarrier.carrier_number', array(
				'label' => 'Carrier #',
				'class' => 'Text100',
				'readonly' => 'readonly',
				'class' => 'ReadOnly',
				'after' => $this->data['Carrier']['name']
			));
		?>
		
		<div class="FormColumn">
			<?php	
				echo $form->input('HcpcCarrier.allowable_sale');
				echo $form->input('HcpcCarrier.allowable_rent');
				echo $form->input('HcpcCarrier.previous_allowable_sale');
				echo $form->input('HcpcCarrier.previous_allowable_rent');
				echo $form->input('HcpcCarrier.allowable_units');
				
				echo $form->input('HcpcCarrier.rp_code', array(
					'label' => 'RP Code',
					'class' =>'text25',
					'options' =>$rpCodes
				));
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('HcpcCarrier.is_medicare_covered', array(
					'label' => array(
						'text' => 'Is Medicare Covered?',
						'class' => 'Checkbox',
					),
					'div' => array('style' => 'margin: 5px 0px;')
				));
				echo $form->input('HcpcCarrier.is_authorization_required', array(
					'label' => array(
						'text' => 'Authorization Required?',
						'class' => 'Checkbox',
					),
					'div' => array('style' => 'margin: 5px 0px;')
				));
				echo $form->input('HcpcCarrier.use_hcpc_crosswalk', array(
					'label' => array(
						'text' => 'Use ICD9 Crosswalk',
						'class' => 'Checkbox'
					),
					'div' => array('style' => 'margin: 5px 0px;')
				));
				echo $form->input('HcpcCarrier.initial_replacement', array(
					'class' => 'text25',
					'options' => $initialReplacement
				));
				echo $form->input('HcpcCarrier.initial_date', array(
					'label' => 'Initial Date',
					'type' => 'text'
				));
				echo $form->input('HcpcCarrier.discontinued_date', array(
					'type' => 'text'
				));
				echo $form->input('HcpcCarrier.updated_date', array(
					'type' => 'text'
				));
			?>
		</div>
		<div class="FormColumn">
			<?php
				echo $form->input('HcpcCarrier.hcpc_message_reference_number', array(
					'label' => 'HCPC Message Reference Number',
					'class' => 'text75'
				));
				echo $form->input('HcpcCarrier.notes', array(
					'class' => 'Text300'
				));
			?>
		</div>

		<br style="clear: both;" /><br />
				
		<?php		
			if (!$readonly)
			{
				echo $ajax->submit('Save', array(
					'id' => 'HcpcCarrierFormSave',
					'class' => 'StyledButton',
					'div' => array('class' => 'Horizontal'),
					'url' => "/json/hcpcCarriers/edit/{$this->data['HcpcCarrier']['id']}",
					'condition' => 'Modules.Hcpc.Carriers.onBeforePost(event)',
					'complete' => 'Modules.Hcpc.Carriers.onPostCompleted(request)'
				));
			}
			
			echo $form->end();
		?>
		<br class="ClearBoth" /><br />
	</div>
</div>

<br />

<div class="GroupBox" id="HcpcCarrierModifierDetail">
	<h2>HCPC Carrier Modifier Associations</h2>
	<div class="Content">
		<?php
			if (!$readonly)
			{
				echo $form->input('HcpcModifier.hcpc_modifier', array(
					'label' => 'HCPC Modifier',
					'class' => 'Text400',
					'div' => array('class' => 'FormColumn'),
					'options' => $modifierDropDown
				));
	
				echo $html->link($html->image('iconAdd.png', array('style' => 'margin-top: 12px')), '#', array('escape' => false, 'id' => 'ModifierAddLink'));
				echo '<br /><br />';
			}
		?>
		
		<?php if (!$readonly): ?>
			<table id="ModifiersTable" class="Styled" style="width: 500px;<?php if (count($modifiers) == 0): ?> display: none;<?php endif; ?>" >
					<tr>
						<th>Modifier</th>
						<th style="width: 50px;"></th>
					</tr>
				<?php
					foreach ($modifiers as $row)
					{
						echo $html->tableCells(
							array(
								$form->hidden('modifierAssociationID', array('value' => $row['HcpcModifierAssociations']['id'])) . 
								h($modifierDropDown[$row['HcpcModifierAssociations']['hcpc_modifier']]),
								$html->link($html->image('iconDelete.png'), '#', array('escape' => false, 'class' => 'HcpcModifierDeleteLink'))
							),
							array('class' => 'Auto'),
							array('class' => 'Alt Auto')
						);
					}
				?>
			</table>
		<?php else: ?>
			<table style="width: 500px;">
				<?php
					foreach ($modifiers as $i => $row)
					{
						echo $html->tableCells(
							array(array(
								array(($i + 1) . '.', array('style' => 'width: 20px;')),
								h($modifierDropDown[$row['HcpcModifierAssociations']['hcpc_modifier']])
							)),
							array('class' => 'Auto'),
							array('class' => 'Alt Auto')
						);
					}
				?>
			</table>
		<?php endif; ?>
	</div>
</div>
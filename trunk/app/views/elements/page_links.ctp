<div style="margin-bottom: 2px">
<?= $paginator->prev('Prev') . ' ' .
	$paginator->numbers(array('modulus' => '4', 'first' => 1, 'last' => 1)) . ' ' .
	$paginator->next('Next');
?>
</div>
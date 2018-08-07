<?php 
	$options = array('required' => 'required', 'class' => 'form-control select2autocompleteAjax input-md', "autofocus" => "autofocus");
?>
<div class="form-group drLigne">
	<div class="col-xs-3">
		<?php echo $form['produit']->render(array('class' => 'form-control input select2 select2-offscreen select2autocomplete liste-produits', 'placeholder' => "Produit")) ?>
	</div>
	<div class="col-xs-2">
		<?php echo $form['complement']->render(array('class' => 'form-control input complement-produit', 'placeholder' => "Complément")) ?>
	</div>
	<div class="col-xs-2">
		<?php echo $form['bailleur']->render(array_merge($options, array('placeholder' => "Bailleur", 'class' => $options['class'].' bailleur-produit'))) ?>
	</div>
	<div class="col-xs-2">
		<?php echo $form['categorie']->render(array('class' => 'form-control input select2 select2-offscreen select2autocomplete categorie-produit', 'placeholder' => "Catégorie")) ?>
	</div>
	<div class="col-xs-1">
		<?php echo $form['valeur']->render(array('class' => 'form-control input text-right', 'placeholder' => "Volume / Valeur")) ?>
	</div>
	<div class="col-xs-2">
		<div class="col-xs-10" style="padding-right: 0px !important; padding-left: 0px !important;">
			<?php echo $form['tiers']->render(array_merge($options, array('placeholder' => "Tiers"))) ?>
		</div>
		<div class="col-xs-2" style="padding-right: 0px !important; padding-left: 0px !important;">
		<a href="javascript:void(0)" data-container="div.drLigne" role="button" class="text-danger btn_rm_ligne_template" style="font-size: 20px;"><span class="glyphicon glyphicon-remove-sign"></span></a>
		</div>
	</div>
	
	
</div>
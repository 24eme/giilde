<section id="principal">
  <ol class="breadcrumb">
      <li><a href="<?php echo url_for('annuaire', array('identifiant' => $etablissementPrincipal->identifiant)); ?>" class="active">Contrat</a></li>
      <li><a href="<?php echo url_for('vrac_societe', array('identifiant' => $etablissementPrincipal->identifiant)); ?>" class="active">Annuaire</a></li>
  </ol>
		<h2 class="titre_principal">Ajouter un contact</h2>
		<form id="principal" method="post" action="<?php echo url_for('annuaire_selectionner', array('identifiant' => $identifiant)); ?><?php if (isset($redirect)): ?>?redirect=<?php echo $redirect ?><?php endif; ?>">
				<?php echo $form->renderHiddenFields() ?>
				<?php echo $form->renderGlobalErrors() ?>
				<div class="row">
							<div class="col-xs-12">
				<div class="panel panel-default">
						<div class="panel-heading"><stong>Saisissez ici le type et l'identifiant du tiers que vous souhaitez ajouter à votre annuaire</strong></div>
							<div class="panel-panel">
					<div class="row">
							<div class="col-xs-12">
						          <ul class="list-group" style="margin-bottom:0px;">
											<li class="list-group-item" >
														  <div class="row">

						<div class="col-xs-4">Type</div>
						<div class="col-xs-8">Identifiant</div>
						</div>
					</li>
					<li class="list-group-item" >
					<div class="row">
						<div class="col-xs-4"><?php if($isCourtierResponsable && (isset($form['type']))): ?>
							<span><?php echo $form['type']->renderError() ?></span>
								<?php echo $form['type']->render() ?>
							<?php else: ?>
								Viticulteur
							<?php endif; ?>
						</div>
						<div class="col-xs-8">
							<span><?php echo $form['tiers']->renderError() ?></span>
							<?php echo $form['tiers']->render() ?>
						</div>
					</div>
				</li>
			</ul>
		</div>
		</div>
</div>
</div>
</div>
</div>
					<div class="row">
						<div class="col-xs-12">
		            <a class="btn btn-default" href="<?php echo url_for('annuaire_retour', array('identifiant' => $identifiant)) ?>">Retour</a>
			    			<button type="submit" name="valider" class="btn btn-success pull-right" >Valider</button>
						</div>
				</div>

		</form>
    <!-- <?php include_partial('vrac/popup_notices'); ?> -->
</section>

<?php

// include_partial('vrac/colonne_droite', array('societe' => $societe, 'etablissementPrincipal' => $etablissementPrincipal));

?>

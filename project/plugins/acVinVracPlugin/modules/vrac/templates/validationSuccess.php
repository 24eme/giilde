<?php
use_helper('Float');
use_helper('Vrac');
use_helper('PointsAides');
?>

<?php include_partial('vrac/breadcrumbSaisie', array('vrac' => $vrac, 'isTeledeclarationMode' => $isTeledeclarationMode, 'etablissementPrincipal' => $etablissementPrincipal)); ?>

<section id="principal" class="vrac">

<?php include_component('vrac', 'etapes', array('vrac' => $vrac, 'compte' => $compte, 'actif' => 4, 'urlsoussigne' => null, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<form action="" method="post" class="form-horizontal" id="contrat_validation" >
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>

<div class="row">
<?php include_partial("vrac/recap", array('vrac' => $vrac, 'isTeledeclarationMode' => $isTeledeclarationMode, 'template_validation' => 1)); ?>
</div>

<div class="row">
    <div class="col-xs-12">
      <?php if($validation->hasErreurs()): ?>
      <div class="alert alert-danger">
          <strong>Points bloquants</strong><?php echo getPointAideHtml('vrac','validation_pt_bloquant'); ?>
          <?php include_partial('document_validation/validationType', array('points' => $validation->getPoints('erreur'))) ?>
      </div>
      <?php endif; ?>

      <?php if($validation->hasVigilances()): ?>
      <div class="alert alert-warning">
          <strong>Points de vigilance</strong><?php echo getPointAideHtml('vrac','validation_pt_vigilance'); ?>
          <?php include_partial('document_validation/validationType', array('points' => $validation->getPoints('vigilance'))) ?>
      </div>
      <?php endif; ?>
    </div>
</div>

<?php if ($validation->isValide() && !$isTeledeclarationMode) : ?>
<div>

                <?php if (isset($form['date_signature'])): ?>
                    <?php echo $form['date_signature']->renderError(); ?>
                    <div class="form-group">
                        <?php echo $form['date_signature']->renderError(); ?>
		                <?php echo $form['date_signature']->renderLabel("Date de signature :", array('class' => 'col-sm-8 control-label')); ?>
		                <div class="col-sm-4">
		                    <?php echo $form['date_signature']->render(); ?>
		                </div>
                    </div>
                 <?php endif; ?>

</div>
<?php endif; ?>

<div class="row">
  <div class="col-xs-4  text-left">
      <a tabindex="-1"  href="<?php echo url_for('vrac_condition',$vrac); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Etape précédente</a>
  </div>
    <div class="col-xs-4 text-center">
        <?php if ($isTeledeclarationMode && $vrac->isBrouillon()) : ?>
            <a tabindex="-1" class="btn btn-danger" href="<?php echo url_for('vrac_supprimer_brouillon', $vrac); ?>" style="margin-left: 10px">Supprimer le brouillon</a>
        <?php endif; ?>
        <?php if (!$isTeledeclarationMode) : ?>
                <a tabindex="-1" href="<?php echo url_for('vrac'); ?>" class="btn btn-default" ><span class="glyphicon glyphicon-floppy-disk"></span> Enregistrer en brouillon</a>
            <?php endif; ?>
    </div>
    <?php if ($validation->isValide()) : ?>
        <?php if ($isTeledeclarationMode): ?>
            <?php if ($signatureDemande): ?>
              <div class="col-xs-2 pull-right">
                    <a data-toggle="modal" data-target="#signature_popup_content" class="signature_popup btn btn-success pull-right">Signer le contrat <span class="glyphicon glyphicon-ok"></span></a>
              </div>
              <?php include_partial('signature_popup', array('vrac' => $vrac, 'societe' => $societe, 'etablissementPrincipal' => $etablissementPrincipal, 'validation' => true)); ?>
            <?php endif; ?>
        <?php else: ?>
           <div class="col-xs-4 text-right">
            <button class="btn btn-success" type="submit">Terminer la saisie <span class="glyphicon glyphicon-ok"></span></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div class="modal" id="confirm" tabindex="-1" role="dialog" aria-labelledby="Confirmation">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Confirmez vous la saisie du contrat :</h4>
      </div>
      <div class="modal-body">
      	<div class="text-center">

        	<p>
            	<span class="<?php echo typeToPictoCssClass($vrac->type_transaction) ?>" style="font-size: 24px;"><?php echo "&nbsp;Contrat de " . showType($vrac); ?></span>
            </p>
        <?php if (in_array($vrac->type_transaction, array(VracClient::TYPE_TRANSACTION_VIN_VRAC, VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE))): ?>
    		<h3><?php echo $vrac->produit_libelle ?> <small><?php echo ($vrac->millesime)? $vrac->millesime : 'Non millésimé'; ?><?php if ($vrac->get('millesime_85_15')): ?> (85/15)<?php endif;?></small></h3>
    		<?php if ($vrac->cepage): ?>
            Cépage : <strong><?php echo $vrac->cepage_libelle ?><?php if ($vrac->get('cepage_85_15')): ?> (85/15)<?php endif;?></strong><br />
            <?php endif; ?>
    	<?php else: ?>
    		<h3><?php echo $vrac->cepage_libelle ?> <small><?php if ($vrac->get('cepage_85_15')): ?> (85/15)<?php endif;?></small></h3>
    		<?php if ($vrac->produit_libelle): ?>
            Revendiquable en <strong><?php echo $vrac->produit_libelle ?></strong><br />
            <?php endif; ?>
    	<?php endif; ?>
    	</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-success">Confirmer</button>
      </div>
    </div>
  </div>
</div>

</form>
</section>
<br/>

<?php
use_helper('PointsAides');
?>
<?php if (!isset($soussigne)) {
    $soussigne = null;
    if (isset($id)) {
        $soussigne = EtablissementClient::getInstance()->find($id);
    }
} ?>
<?php if (!$soussigne) return ; ?>
<strong><?php echo $soussigne->nom ?></strong>
<small class="text-muted">(<?php echo EtablissementFamilles::getFamilleLibelle($soussigne->famille) ?>)</small><br />
<?php echo $soussigne->siege->adresse ?> <?php echo $soussigne->siege->code_postal ?> <?php echo $soussigne->siege->commune ?><br />
<?php if($soussigne->email || $soussigne->telephone ): ?>
<?php echo $soussigne->email ?> <?php echo $soussigne->telephone ?><br />
<?php endif; ?>
<?php if($soussigne->cvi): ?>
<span class="text-muted">CVI : <strong><?php echo $soussigne->cvi ?></strong></span>
<?php endif; ?>
<?php if($soussigne->cvi): ?>
<span class="text-muted">N° Accise : <strong><?php echo $soussigne->no_accises ?></strong></span>
<?php endif; ?>
<?php if($soussigne->carte_pro): ?>
<br /><span class="text-muted">N° Carte professionnel : <strong><?php echo $soussigne->carte_pro ?></strong></span>
<?php endif; ?>
<?php if($isTeledeclarationMode && (!$soussigne->exist('teledeclaration_email') || !$soussigne->teledeclaration_email)): ?>
  <br />
  <br />
  <div class="alert alert-warning" role="warning">
  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
  <span class="sr-only">Error:</span>
  Ce ressortissant n'a pas encore activé son compte de télédeclarant.
  <?php echo getPointAideHtml('vrac','soussigne_nonactif_compte'); ?>
</div>
<?php endif; ?>

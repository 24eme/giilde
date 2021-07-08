<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>
<?php include_partial('dsnegoce/preTemplate'); ?>
<?php include_partial('dsnegoce/breadcrum', array('etablissement' => $etablissement)); ?>

<div class="row">
    <div class="col-xs-12 formEtablissement">
        <?php include_component('dsnegoce', 'formEtablissementChoice', array('identifiant' => $etablissement->_id)) ?>
    </div>
</div>

<h1>Déclaration de Stock</h1>

<div class="col-xs-12">
    <div class="row">
      <h4>
          <form class="form-inline pull-right" method="get">
              <?php echo $formPeriodes->renderGlobalErrors() ?>
              <?php echo $formPeriodes->renderHiddenFields() ?>
              Stock à fin :
              <div class="form-group<?php if($formPeriodes['date']->hasError()): ?> has-error<?php endif; ?>" style="width: 160px;">
                  <?php echo $formPeriodes['date']->renderError(); ?>
                  <?php echo $formPeriodes['date']->render(); ?>
              </div>
              <button type="submit" class="btn btn-default">Changer</button>
          </form>
        </h4>
    </div>

    <p>&nbsp;</p>

    <div class="row">
      <?php if (!$docRepriseProduits): ?>
        <p>La saisie des stocks n'est pas possible car nous n'avez pas saisie votre DRM de <strong><?php echo (format_date($date, 'MMMM yyyy', 'fr_FR')) ?></strong></p>
      <?php elseif(!$dsnegoce): ?>
        <a href="<?php echo url_for('dsnegoce_creation', ['identifiant' => $etablissement->identifiant, 'date' => $date]) ?>" class="btn btn-primary">Saisir les stocks</a>
      <?php elseif($dsnegoce->isValidee()): ?>
        <?php include_partial('dsnegoce/recap', array('dsnegoce' => $dsnegoce)); ?>
        <a href="<?php echo url_for('dsnegoce_visualisation', $dsnegoce) ?>" class="btn btn-primary pull-right">Accéder à la déclaration&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
      <?php else: ?>
        <p>Une déclaration de stocks au <?php echo (format_date($dsnegoce->date_stock, 'dd MMMM yyyy', 'fr_FR')) ?> est en cours de saisie</p>
        <a href="<?php echo url_for('dsnegoce_infos', $dsnegoce) ?>" class="btn btn-primary pull-right">Reprendre la saisie&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
      <?php endif; ?>
    </div>

</div>

<?php include_partial('dae/postTemplate'); ?>

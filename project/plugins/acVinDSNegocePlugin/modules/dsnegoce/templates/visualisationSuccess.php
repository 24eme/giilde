<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>
<?php include_partial('dsnegoce/preTemplate'); ?>
<?php include_partial('dsnegoce/breadcrum', array('etablissement' => $etablissement)); ?>

<section id="principal">
    <h1>
      Déclaration de Stock au <?php echo (format_date($dsnegoce->date_stock, 'dd MMMM yyyy', 'fr_FR')) ?>
      <span class="text-muted pull-right"><?php if($dsnegoce->teledeclare): ?>Télédéclarée et <?php endif; ?>Validée le <?php echo (format_date($dsnegoce->valide->date_signee, 'dd/MM/yyyy', 'fr_FR')) ?></span>
    </h1>

    <?php include_partial('dsnegoce/recap', array('dsnegoce' => $dsnegoce)); ?>

    <?php if (!$isTeledeclarationMode): ?>
    <div class="row" style="margin-top: 20px;">
        <div class="col-xs-6">
            <a class="btn btn-default" tabindex="-1" href="<?php echo url_for('dsnegoce_etablissement', $etablissement) ?>"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Retour</a>
        </div>
        <div class="col-xs-6 text-right">
            <a onclick="return confirm('Confirmez-vous la réouverture de la déclaration')" class="btn btn-warning" href="<?php echo url_for('dsnegoce_devalidate', $dsnegoce) ?>">Réouvrir la déclaration</span></a>
        </div>
    </div>
  <?php endif; ?>

</div>

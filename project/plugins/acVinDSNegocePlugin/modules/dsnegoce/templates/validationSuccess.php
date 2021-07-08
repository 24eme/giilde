<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>
<?php include_partial('dsnegoce/preTemplate'); ?>
<?php include_partial('dsnegoce/breadcrum', array('etablissement' => $etablissement)); ?>

<section id="principal">
    <?php include_partial('dsnegoce/etapes', array('dsnegoce' => $dsnegoce)); ?>
    <p>Dans cette étape, vous devez controler votre saisie et valider l'ensemble des stocks</p>
    <?php include_partial('dsnegoce/recap', array('dsnegoce' => $dsnegoce)); ?>


    <div class="row" style="margin-top: 20px;">
        <div class="col-xs-6">
            <a class="btn btn-default" tabindex="-1" href="<?php echo url_for('dsnegoce_stocks', $dsnegoce) ?>"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Étape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
            <a onclick="return confirm('Confirmez-vous la validation de votre déclaration')" class="btn btn-success" href="<?php echo url_for('dsnegoce_validate', $dsnegoce) ?>">Valider la déclaration&nbsp;<span class="glyphicon glyphicon-ok"></span></a>
        </div>
    </div>

</div>

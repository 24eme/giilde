<?php include_partial('facture/preTemplate'); ?>

<ol class="breadcrumb">
    <li class="active"><a href="<?php echo url_for('facture') ?>">Factures</a></li>
</ol>

<div class="row">
    <div class="col-xs-12" id="formEtablissementChoice">
        <?php include_component('facture', 'chooseSociete'); ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <h2>Génération des factures</h3>
        <?php include_partial('historiqueGeneration', array('generations' => $generations)); ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <h3>Générer toutes les factures</h3>
        <?php include_component('facture','generationMasse'); ?>
    </div>
</div>

<hr />

<div class="row">
    <div class="col-xs-12">
        <h2>Facturation libre</h3>
        <a href="<?php echo url_for('facture_mouvements'); ?>" class="btn btn-md btn-default">Créer des mouvements de facturation libre</a>
    </div>
</div>

<?php include_partial('facture/postTemplate'); ?>
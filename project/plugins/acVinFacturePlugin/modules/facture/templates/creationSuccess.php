<?php
use_helper('Float');
?>
<?php include_partial('facture/preTemplate'); ?>

<ol class="breadcrumb">
    <li class="visited"><a href="<?php echo url_for('facture') ?>">Page d'accueil</a></li>
    <li class="visited"><a href="<?php echo url_for('facture_societe', $societe) ?>"><?php echo $societe->raison_sociale ?> (<?php echo $societe->identifiant ?>)</a></li>
    <li class="active"><a href="<?php echo url_for('facture_creation', $societe) ?>" class="active">Génération de factures</a></li>
</ol>

<div class="row">
    <div class="col-xs-12" id="formEtablissementChoice">
        <?php include_component('facture', 'chooseSociete', array('identifiant' => $societe->identifiant)); ?>
    </div>
    <div class="col-xs-12">
        <h2>Génération de facture</h2>
        <?php if ($sf_user->hasFlash('notice')): ?>
            <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
        <?php endif; ?>

        <?php if ($sf_user->hasFlash('error')): ?>
            <div class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('error') ?></div>
        <?php endif; ?>

        <?php include_partial('facture/generationMasse', array('generationForm' => $form,'massive' => false,'identifiant' => $societe->identifiant)); ?>
    </div>
</div>
<?php include_partial('facture/postTemplate'); ?>

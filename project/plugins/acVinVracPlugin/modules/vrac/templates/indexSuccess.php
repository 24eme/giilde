<?php use_helper('Vrac'); ?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('vrac') ?>" class="active">Contrats</a></li>
</ol>

<div class="row">
    <div class="col-xs-12">
        <?php include_component('vrac', 'formEtablissementChoice') ?>
    </div>

    <div class="col-xs-12">
        <h3>Création d'un contrat</h3>
        <form action="<?php echo url_for('vrac'); ?>" method="post" class="form-inline">
            <?php echo $creationForm->renderHiddenFields() ?>
            <?php echo $creationForm->renderGlobalErrors() ?>
            <?php echo $creationForm['annee']->renderError(); ?>
            <?php echo $creationForm['bordereau']->renderError(); ?>
            <div class="form-group<?php if($creationForm['annee']->hasError()): ?> has-error<?php endif; ?>">
                <?php echo $creationForm['annee']->render(array('placeholder' => 'AAAA')); ?>
            </div>
            <div class="form-group<?php if($creationForm['bordereau']->hasError()): ?> has-error<?php endif; ?>">
                <?php echo $creationForm['bordereau']->render(array('placeholder' => 'N° bordereau')); ?>
            </div>
            <button type="submit" class="btn btn-default">Créer le contrat</button>
        </form>
    </div>

    <div class="col-xs-12">
        <h3>Les derniers contrats saisis</h3>
        <?php include_partial('list', array('vracs' => $vracs)); ?>
    </div>
</div>

<?php use_helper('Float'); ?>
<?php use_helper('Date'); ?>
<?php use_javascript('facture.js'); ?>

<ol class="breadcrumb">
    <li class="visited"><a href="<?php echo url_for('facture') ?>">Factures</a></li>
    <li class="visited"><a href="<?php echo url_for('facture_mouvements') ?>">Facturation libre</a></li>
    <li class="active"><a href="<?php echo url_for('facture_mouvements_edition', array('id' => $form->getObject()->_id)) ?>" class="active">Facture libre n°&nbsp;<?php echo $form->getObject()->identifiant; ?></a></li>
</ol>

<h2>Edition de la facture libre n°&nbsp;<?php echo $form->getObject()->identifiant; ?></h2>

<form id="form_mouvement_edition_facture" action="" method="post" class="form-horizontal">

    <?php if ($form instanceof sfForm && ($form->hasErrors() || $form->hasGlobalErrors())): ?>
        <ul class="error_list">
            <?php foreach ($form->getGlobalErrors() as $item): ?>
                <li><?php echo $item->getMessage(); ?></li>
            <?php endforeach; ?>
            <?php include_partial('drm/errorMessagesFromFormFieldSchema', array('form_field_schema' => $form->getFormFieldSchema())) ?>
        </ul>
    <?php endif; ?>

    <?php foreach ($form->getErrorSchema() as $item): ?>

        <div class="alert alert-danger" role="alert">
            <?php echo $item->getMessage(); ?>
        </div>
        <?php
    endforeach;
    ?>

    <div class="row row-margin">
        <div class="col-xs-6">
            <div class="row">
                <div class="col-xs-12"><?php echo $form['libelle']->renderError(); ?>  </div>
                <div class="col-xs-12"><?php echo $form['libelle']->renderLabel(); ?>  </div>
                <div class="col-xs-12"><?php echo $form['libelle']->render(array('class' => 'form-control input-lg text-right')); ?>  </div>
            </div>


        </div>
        <div class="col-xs-6">
            <div class="row">
                <div class="col-xs-12 text-right">
                    <?php if ($form->getObject()->getDate()): ?>
                        <span >Facture Libre du <?php echo format_date($form->getObject()->getDate(), "dd/MM/yyyy", "fr_FR") ?></span>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
    <hr />

    <div class="row row-margin">
        <div class="col-xs-12" style="border-bottom: 1px dotted #d2d2d2;" id="mouvementsfacture_list"  data-template="#template_mouvementfacture">
            <div class="row">
                <div class="col-xs-4 text-center lead text-muted">Identité</div>
                <div class="col-xs-2 text-center lead text-muted">Lien comptable</div>
                <div class="col-xs-3 text-center lead text-muted">Libellé article</div>
                <div class="col-xs-2 text-center lead text-muted">Prix&nbsp;U.</div>
                <div class="col-xs-1 text-left lead text-muted" style="padding-left: 0;">Quantité</div>
            </div>
            <?php
            foreach ($form['mouvements'] as $key => $mvtForm):
                $itemKeys = split('_', $key);
                $item = ($factureMouvements->mouvements->exist($itemKeys[0]) && $factureMouvements->mouvements->get($itemKeys[0])->exist($itemKeys[1])) ?
                        $factureMouvements->mouvements->get($itemKeys[0])->get($itemKeys[1]) : null;
                if (!preg_match('/^nouveau/', $key) || !$factureMouvements->mouvements->exist(str_replace('_', '/', $key))):
                    ?>

                    <?php include_partial('itemMouvementFacture', array('mvtForm' => $mvtForm, 'item' => $item)); ?>

                    <?php
                endif;
            endforeach;
            ?>
            <?php include_partial('templateMouvementFactureItem', array('mvtForm' => $form->getFormTemplate(), 'mvtKey' => $form->getNewMvtId())); ?>
        </div>
        <?php echo $form->renderHiddenFields(); ?>
    </div>
    <br/>
    <div class="row row-margin">
        <div class="col-xs-6 text-left">
            <a class="btn btn-danger btn-lg btn-upper" tabindex="-1" href="<?php echo url_for('facture_mouvements') ?>">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
            <input type="button" class="btn btn-success btn-lg btn-upper" value="Valider" onclick="this.form.submit();" />
        </div>
    </div>

</form>

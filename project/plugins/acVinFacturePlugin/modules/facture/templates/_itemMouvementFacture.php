<?php
?>
<div class="form-group line mvt_ligne" >
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-4 <?php if($mvtForm["identifiant"]->hasError()): ?>text-danger<?php endif; ?>">
                <?php echo $mvtForm['identifiant']->renderError(); ?>
                <?php echo $mvtForm['identifiant']->render(array('class' => 'form-control select2autocomplete input-md', 'autofocus' => 'autofocus', 'placeholder' => 'Rechercher')); ?>
            </div>
            <div class="col-xs-2 <?php if($mvtForm["identifiant_analytique"]->hasError()): ?>text-danger<?php endif; ?>" style="padding-left: 0; padding-right: 0;">
                <?php echo $mvtForm['identifiant_analytique']->renderError(); ?>
                <?php echo $mvtForm['identifiant_analytique']->render(array('class' => 'form-control input-md text-right select2 identifiant_analytique')); ?>
                <?php if(isset($item) && $item && $item->facture): ?>
                <input class="form-control input-md" disabled="disabled" type="text" value="<?php echo $item->identifiant_analytique_libelle_compta; ?>" />
                <?php endif; ?>
            </div>
            <div class="col-xs-3 <?php if($mvtForm["libelle"]->hasError()): ?>text-danger<?php endif; ?>" style="padding-right: 0;">
                <?php echo $mvtForm['libelle']->renderError(); ?>
                <?php echo $mvtForm['libelle']->render(array('class' => 'form-control input-md select2-libelle')); ?>
            </div>
            <div class="col-xs-2 <?php if($mvtForm["prix_unitaire"]->hasError()): ?>text-danger<?php endif; ?>">
                <?php echo $mvtForm['prix_unitaire']->renderError(); ?>
                <?php echo $mvtForm['prix_unitaire']->render(array('class' => 'form-control input-md text-right input-float')); ?>
            </div>
            <div class="col-xs-1 <?php if($mvtForm["quantite"]->hasError()): ?>text-danger<?php endif; ?>" style="padding-left: 0;">
                <?php echo $mvtForm['quantite']->renderError(); ?>
                <?php echo $mvtForm['quantite']->render(array('class' => 'form-control input-md text-right')); ?>
            </div>
            <div style="position: absolute; right: -10px; top: 3px;" class="row mouvements_facture_delete_row">
                <a href="#" class="btn btn-default btn-xs btn_supprimer_ligne_template" tabindex="-1" data-container="#mouvementfacture_list" <?php if(isset($item) && $item && $item->facture): ?> disabled="disabled"  <?php endif;?>><span class="glyphicon glyphicon-remove"></span></a>
            </div>
        </div>
    </div>
</div>

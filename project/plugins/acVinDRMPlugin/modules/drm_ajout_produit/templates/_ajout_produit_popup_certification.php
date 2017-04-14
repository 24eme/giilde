<div class="modal modal-autoshow">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Ajouter un produit</h4>
            </div>
            <form class="form-horizontal" action="<?php echo url_for('drm_choix_produit_add_produit', array('identifiant' => $drm->identifiant, 'periode_version' => $drm->getPeriodeAndVersion(), 'add_produit' => $form->getProduitFilter())) ?>" method="post">
            <div class="modal-body">
                <?php echo $form->renderHiddenFields(); ?>
                <?php echo $form->renderGlobalErrors(); ?>
                <div class="form-group">
                    <div class="col-sm-12">
                    <?php echo $form->renderHiddenFields(); ?>
                    <?php echo $form->renderGlobalErrors(); ?>
                    <div class="row">
                        <div class="col-sm-4 text-right">
                          <?php echo $form['produit']->renderLabel(); ?>
                        </div>
                        <div class="col-sm-8 text-left">
                          <?php echo $form['produit']->render(array('class' => 'select2 form-control')); ?>
                        </div>
                    </div>
                    </div>
                    <div class="col-sm-12">
                      <br/>
                      <div class="row">
                        <div class="col-sm-4 text-right">
                          <?php echo $form['denomination_complementaire']->renderLabel(); ?>
                        </div>
                        <div class="col-sm-8 text-left">
                          <?php echo $form['denomination_complementaire']->render(array('class' => 'form-control', 'placeholder' => 'Exemple : millesime, unité géographique plus petite')); ?>
                        </div>
                      </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Abandonner</button>
                <button type="submit" class="btn btn-success">Ajouter le produit</button>
            </div>
            </form>
        </div>
    </div>
</div>

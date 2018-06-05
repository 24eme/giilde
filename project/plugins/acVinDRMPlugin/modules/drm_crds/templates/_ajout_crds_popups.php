<div class="modal modal-autoshow">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Ajouter un type de CRD</h4>
            </div>
            <form class="form-horizontal" action="<?php echo url_for('drm_ajout_crd', $form->getObject()); ?>?add_crd=<?php echo $regime; ?>" method="post">
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <?php echo $form->renderHiddenFields(); ?>
                        <?php echo $form->renderGlobalErrors(); ?>
                        <?php echo $form['couleur_crd_'.$regime]->renderError(); ?>
                        <div class="form-group">
                            <?php echo $form['couleur_crd_'.$regime]->renderLabel(null, array('class' => 'col-sm-4 control-label')) ?>
                            <div class="col-sm-8">
                                <?php echo $form['couleur_crd_'.$regime]->render(); ?>
                            </div>
                        </div>
                        <?php echo $form['litrage_crd_'.$regime]->renderError(); ?>
                        <div class="form-group">
                            <?php echo $form['litrage_crd_'.$regime]->renderLabel(null, array('class' => 'col-sm-4 control-label')) ?>
                            <div class="col-sm-8">
                                <?php echo $form['litrage_crd_'.$regime]->render(); ?>
                            </div>
                        </div>
                        <?php echo $form['stock_debut_'.$regime]->renderError(); ?>
                        <div class="form-group">
                            <?php echo $form['stock_debut_'.$regime]->renderLabel(null, array('class' => 'col-sm-4 control-label')) ?>
                            <div class="col-sm-4">
                            <?php echo $form['stock_debut_'.$regime]->render(); ?>
                            </div>
                        </div>
                        <?php echo $form['genre_crd_'.$regime]->renderError(); ?>
                        <div class="form-group">
                                <?php echo $form['genre_crd_'.$regime]->renderLabel(null, array('class' => 'col-sm-4 control-label')) ?>
                            <div class="col-sm-8">
                                <?php echo $form['genre_crd_'.$regime]->render(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Abandonner</button>
                <button type="submit" class="btn btn-success">Ajouter une ligne CRD</button>
            </div>
            </form>
        </div>
    </div>
</div>

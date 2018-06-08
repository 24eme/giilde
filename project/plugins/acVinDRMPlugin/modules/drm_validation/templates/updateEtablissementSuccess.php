<?php use_helper('DRM'); ?>
<?php use_helper("Date"); ?>

<?php include_partial('drm/breadcrumb', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<section id="principal" class="drm">
        <?php include_partial('drm/etapes', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'etape_courante' => DRMClient::ETAPE_VALIDATION)); ?>

        <div class="modal modal-autoshow" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="<?php echo url_for('drm_validation_update_etablissement', $drm); ?>" method="POST" class="form-horizontal">
                    <div class="modal-header">
                        <a href="<?php echo url_for('drm_validation', $drm) ?>" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></a>
                        <h3 class="modal-title">Modification des informations de votre chai</h3>
                    </div>
                    <div class="modal-body">
                            <?php echo $form->renderHiddenFields(); ?>
                            <?php echo $form->renderGlobalErrors(); ?>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Raison Sociale :</label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php echo $drm->declarant->nom; ?></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $form['cvi']->renderLabel(null, array("class" => "col-sm-4 control-label")); ?>
                                <div class="col-sm-8">
                                    <?php echo $form['cvi']->renderError(); ?>
                                    <?php echo $form['cvi']->render(); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $form['no_accises']->renderLabel(null, array("class" => "col-sm-4 control-label")); ?>
                                <div class="col-sm-8">
                                    <?php echo $form['no_accises']->renderError(); ?>
                                    <?php echo $form['no_accises']->render(); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $form['adresse']->renderLabel(null, array("class" => "col-sm-4 control-label")); ?>
                                <div class="col-sm-8">
                                    <?php echo $form['adresse']->renderError(); ?>
                                    <?php echo $form['adresse']->render(); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $form['code_postal']->renderLabel(null, array("class" => "col-sm-4 control-label")); ?>
                                <div class="col-sm-8">
                                    <?php echo $form['code_postal']->renderError(); ?>
                                    <?php echo $form['code_postal']->render(); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo $form['commune']->renderLabel(null, array("class" => "col-sm-4 control-label")); ?>
                                <div class="col-sm-8">
                                    <?php echo $form['commune']->renderError(); ?>
                                    <?php echo $form['commune']->render(); ?>
                                </div>
                            </div>
                            <?php if ($drm->declarant->exist('adresse_compta')): ?>
                                <div class="form-group">
                                    <?php echo $form['adresse_compta']->renderLabel(null, array("class" => "col-sm-4 control-label")); ?>
                                    <div class="col-sm-8">
                                        <?php echo $form['adresse_compta']->renderError(); ?>
                                        <?php echo $form['adresse_compta']->render(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-sm-6 text-left">
                                <a href="<?php echo url_for('drm_validation', $drm) ?>" class="btn btn-default">Annuler</a>
                            </div>
                            <div class="col-sm-6 text-right">
                                <button type="submit" class="btn btn-success" id="drm_validation_etablissement_valider_btn">Valider</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
          </div>
    </div>
</section>

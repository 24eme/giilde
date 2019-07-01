<?php use_helper('Date'); ?>
<?php use_helper('DRM'); ?>
<?php use_helper('Orthographe'); ?>
<div id="drm_delete_popup"  class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo url_for('drm_delete', array('identifiant' => $drm->identifiant, 'periode_version' => $drm->getPeriodeAndVersion())); ?>" method="post" >
            <div class="modal-content">
                <div class="modal-header">

                    <h2>Suppression de la DRM <?php echo getFrPeriodeElision($drm->periode); ?></h2>
                </div>
                <div class="modal-body">
                    <?php echo $deleteForm->renderHiddenFields(); ?>
                    <?php echo $deleteForm->renderGlobalErrors(); ?>

                    <p>Étes vous sur(e) de vouloir supprimer cette DRM <?php echo getFrPeriodeElision($drm->periode); ?> ?</p>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Annuler</button>
                    <button id="drm_delete_popup_confirm" type="submit" class="btn btn-danger" style="float: right;" ><span>Supprimer la DRM</span></button>
                </div>

            </div>
        </form>
    </div>
</div>

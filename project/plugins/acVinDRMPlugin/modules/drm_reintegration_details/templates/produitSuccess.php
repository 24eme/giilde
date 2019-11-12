<?php
$detailsKey = $detail->getParent()->getKey();
?>
<div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title text-center"><?php echo $detail->getConfig()->get($catKey)->get($key)->getLibelle() ?><br /><span class="text-muted"><?php echo $detail->getLibelle(ESC_RAW); ?></span></h4>
        </div>
        <form data-related-element="#input_<?php echo $catKey ?>_<?php echo $key ?>_<?php echo $detail->getHashForKey() ?>" class="form-horizontal form-ajax-modal" data-content=".ajax-content" method="post" action="<?php echo url_for('drm_reintegration_details', array('sf_subject' => $detail, 'cat_key' => $catKey, 'key' => $key,'details' => $detailsKey)) ?>">
            <div class="modal-body">
                <div class="ajax-content">
                    <?php include_partial('formContent',array('form' => $form, 'detail' => $detail, 'isTeledeclarationMode' => $isTeledeclarationMode, 'catKey' => $catKey, 'key' => $key)); ?>
                </div>
                <?php include_partial('templateItem', array('form' => $form->getFormTemplate(), 'detail' => $detail, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
            </div>
            <div class="modal-footer">
                <button tabindex="-1" type="button" class="btn btn-default pull-left" data-dismiss="modal">Abandonner</button>
                <button type="submit" data-loading-text="Enregistrement..." class="btn btn-success btn-dynamic-element-submit btn-loading">Valider</button>
            </div>
        </form>
    </div>
</div>

<form action="<?php echo url_for('drm_creation_edi'); ?>" method="post" enctype="<?php echo (isset($enctype) && $enctype)? $enctype : "multipart/form-data"; ?>" >
    <?php echo $creationEdiDrmForm->renderHiddenFields(); ?>
    <?php echo $creationEdiDrmForm->renderGlobalErrors(); ?>

    <?php echo $creationEdiDrmForm['edi-file']->renderError(); ?>
    <?php echo $creationEdiDrmForm['edi-file']->renderLabel() ?>
    <?php echo $creationEdiDrmForm['edi-file']->render(); ?>
    <button type="submit">V</button>
</form>

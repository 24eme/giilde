<?php use_helper('DRM'); ?>
<?php use_helper('Date'); ?>
<?php use_helper('Orthographe'); ?>

<ol class="breadcrumb">
    <?php if (!isset($isTeledeclarationMode) || !$isTeledeclarationMode): ?>
    <li><a href="<?php echo url_for('drm') ?>">DRM</a></li>
    <?php else: ?>
        <li><a href="<?php echo url_for('drm_etablissement', array('identifiant' => $drm->getEtablissementObject()->identifiant)) ?>">DRM</a></li>
    <?php endif; ?>
    <li><a href="<?php echo url_for('drm_etablissement', array('identifiant' => $drm->getEtablissementObject()->identifiant)) ?>"><?php echo $drm->getEtablissementObject()->nom ?> (<?php echo $drm->getEtablissementObject()->identifiant ?>)</a></li>
    <li><a class="active" href="">DRM <?php echo getFrPeriodeElision($drm->periode) ?></a></li>
<?php $notice = sfConfig::get('app_drm_notice'); if ($notice): ?>
    <li class="pull-right"><a href="<?php echo $notice; ?>"><strong class="text-waring">Notice d'aide</strong></a></li>
<?php endif; ?>
</ol>

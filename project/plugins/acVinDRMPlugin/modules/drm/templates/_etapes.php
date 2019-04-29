<nav class="navbar navbar-default ">
    <ul class="nav navbar-nav">
        <?php $cpt_etape = 1; ?>
        <?php if (isset($isTeledeclarationMode) && $isTeledeclarationMode) : ?>
            <?php $actif = ($etape_courante == DRMClient::ETAPE_CHOIX_PRODUITS); ?>
            <?php $past = ((!$actif) && (array_search($drm->etape, DRMClient::$drm_etapes) >= array_search(DRMClient::ETAPE_CHOIX_PRODUITS, DRMClient::$drm_etapes))); ?>
            <li class="<?php if($actif): ?>active<?php endif; ?> <?php if (!$past && !$actif): ?>disabled<?php endif; ?> <?php if ($past && !$actif): ?>visited<?php endif; ?>">
                <a href="<?php echo url_for('drm_choix_produit', $drm); ?>">
                    <span>Produits</span>
                    <small class="hidden">Etape <?php echo $cpt_etape; ?></small>
                </a>
            </li>
        <?php $cpt_etape++; ?>
        <?php endif; ?>
        <?php $hasConfMatierePremiere = DRMConfiguration::getInstance()->hasMatierePremiere(); ?>
        <?php $hasMatierePremiere = $drm->hasMatierePremiere(); ?>
        <?php if (isset($isTeledeclarationMode) && $isTeledeclarationMode && $hasMatierePremiere && $hasConfMatierePremiere) : ?>
            <?php $actif = ($etape_courante == DRMClient::ETAPE_MATIERE_PREMIERE); ?>
            <?php $past = ((!$actif) && (array_search($drm->etape, DRMClient::$drm_etapes) >= array_search(DRMClient::ETAPE_MATIERE_PREMIERE, DRMClient::$drm_etapes))); ?>
            <li class="<?php if($actif): ?>active<?php endif; ?> <?php if (!$past && !$actif): ?>disabled<?php endif; ?> <?php if ($past && !$actif): ?>visited<?php endif; ?>">
                <a href="<?php echo url_for('drm_matiere_premiere', $drm); ?>">
                    <span>Matière Première</span>
                    <small class="hidden">Etape <?php echo $cpt_etape; ?></small>
                </a>
            </li>
        <?php $cpt_etape++; ?>
        <?php endif; ?>
        <?php $actif = ($etape_courante == DRMClient::ETAPE_SAISIE_SUSPENDU); ?>
        <?php $past = ((!$actif) && (array_search($drm->etape, DRMClient::$drm_etapes) >= array_search(DRMClient::ETAPE_SAISIE_SUSPENDU, DRMClient::$drm_etapes))); ?>
        <?php $isDouaneTypeSuspendu = $drm->isDouaneType(DRMClient::TYPE_DRM_SUSPENDU); ?>
        <li style="<?php if(!$isDouaneTypeSuspendu): ?>opacity: 0.5;<?php endif ?>" class="<?php if($actif): ?>active<?php endif; ?> <?php if (!$past && !$actif): ?>disabled<?php endif; ?> <?php if ($past && !$actif && $isDouaneTypeSuspendu): ?>visited<?php endif; ?>">
            <a href="<?php echo url_for('drm_edition_details', array('sf_subject' => $drm, 'details' => DRM::DETAILS_KEY_SUSPENDU)); ?>">
                <span><span class="hidden-md hidden-sm">Mouvements </span>Suspendus</span>
                <small class="hidden">Etape <?php echo $cpt_etape; ?></small>
            </a>
        </li>
        <?php $cpt_etape++; ?>
        <?php if (isset($isTeledeclarationMode) && $isTeledeclarationMode) : ?>
        <?php $past = ((!$actif) && (array_search($drm->etape, DRMClient::$drm_etapes) >= array_search(DRMClient::ETAPE_SAISIE_ACQUITTE, DRMClient::$drm_etapes))); ?>
        <?php $actif = ($etape_courante == DRMClient::ETAPE_SAISIE_ACQUITTE); ?>
        <?php $isDouaneTypeAcquitte = $drm->isDouaneType(DRMClient::TYPE_DRM_ACQUITTE); ?>
        <li style="<?php if(!$drm->isDouaneType(DRMClient::TYPE_DRM_ACQUITTE)): ?>opacity: 0.5;<?php endif ?>" class="<?php if($actif): ?>active<?php endif; ?> <?php if (!$past && !$actif): ?>disabled<?php endif; ?> <?php if ($past && !$actif && $isDouaneTypeAcquitte): ?>visited<?php endif; ?>">
            <a href="<?php echo url_for('drm_edition_details', array('sf_subject' => $drm, 'details' => DRM::DETAILS_KEY_ACQUITTE)); ?>">
                <span><span class="hidden-md hidden-sm">Mouvements </span>Acquittés</span>
                <small class="hidden">Etape <?php echo $cpt_etape; ?></small>
            </a>
        </li>
        <?php $cpt_etape++; ?>
        <?php endif; ?>
        <?php if (isset($isTeledeclarationMode) && $isTeledeclarationMode) : ?>
            <?php $actif = ($etape_courante == DRMClient::ETAPE_CRD); ?>
            <?php $past = ((!$actif) && (array_search($drm->etape, DRMClient::$drm_etapes) >= array_search(DRMClient::ETAPE_CRD, DRMClient::$drm_etapes))); ?>
            <li class="<?php if($actif): ?>active<?php endif; ?> <?php if (!$past && !$actif): ?>disabled<?php endif; ?> <?php if ($past && !$actif): ?>visited<?php endif; ?>">
               <a href="<?php echo url_for('drm_crd', $drm); ?>">
                    <span>CRD</span>
                    <small class="hidden">Etape <?php echo $cpt_etape; ?></small>
                </a>
            </li>
        <?php $cpt_etape++; ?>
        <?php endif; ?>
        <?php if (isset($isTeledeclarationMode) && $isTeledeclarationMode) : ?>
            <?php $actif = ($etape_courante == DRMClient::ETAPE_ADMINISTRATION); ?>
            <?php $past = ((!$actif) && (array_search($drm->etape, DRMClient::$drm_etapes) >= array_search(DRMClient::ETAPE_ADMINISTRATION, DRMClient::$drm_etapes))); ?>
            <li class="<?php if($actif): ?>active<?php endif; ?> <?php if (!$past && !$actif): ?>disabled<?php endif; ?> <?php if ($past && !$actif): ?>visited<?php endif; ?>">
                <a href="<?php echo url_for('drm_annexes', $drm); ?>">
                    <span>Annexes</span>
                    <small class="hidden">Etape <?php echo $cpt_etape; ?></small>
                </a>
            </li>
        <?php $cpt_etape++; ?>
        <?php endif; ?>
        <?php $actif = ($etape_courante == DRMClient::ETAPE_VALIDATION); ?>
        <?php $past = ((!$actif) && (array_search($drm->etape, DRMClient::$drm_etapes) >= array_search(DRMClient::ETAPE_VALIDATION, DRMClient::$drm_etapes))); ?>
        <li class="<?php if($actif): ?>active<?php endif; ?> <?php if (!$past && !$actif): ?>disabled<?php endif; ?> <?php if ($past && !$actif): ?>visited<?php endif; ?>">
            <a href="<?php echo url_for('drm_validation', $drm); ?>">
               <span>Validation</span>
               <small class="hidden">Etape <?php echo $cpt_etape; ?></small>
            </a>
        </li>
        <?php $cpt_etape++; ?>
    </ul>
</nav>

<?php use_helper('DRM'); ?>

<div class="panel panel-default <?php echo getClassGlobalEtatDRMCalendrier($isTeledeclarationMode, $calendrier, $periode, $etablissement); ?>">
    <div class="panel-heading text-center"><?php echo $calendrier->getPeriodeLibelle($periode) ?></div>
    <div class="panel-body text-center">
        <?php if($multiEtablissement): ?>
        <p>Chaque carré représente la DRM <br />de l'un de vos chais</p>
        <?php endif; ?>
        <?php foreach ($calendrier->getEtablissements() as $etb): ?>
            <div id="calendrier_item_<?php echo $periode ?>_<?php echo $etb->identifiant ?>" style="<?php if($multiEtablissement): ?>display: none;<?php endif; ?>">
                <div class="text-center">
                    <p class="etablissement_nom"><?php echo $etb->nom; ?></p>
                    <p class="etablissement_identifiant"><?php echo $etb->identifiant; ?></p>
                    <p class="lignestatut">Etat : <span class="statut"><?php echo getEtatDRMCalendrier($isTeledeclarationMode, $calendrier, $periode, $etb); ?></span>&nbsp;<?php echo getPointAideHtml('drm','etats') ?><br/>&nbsp;<?php echo getTeledeclareeLabelCalendrier($isTeledeclarationMode && !(sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte()), $calendrier, $periode) ?></p>
                    <?php $lien = getEtatDRMHrefCalendrier($isTeledeclarationMode, $calendrier, $periode, $etb); ?>
                    <?php if ($lien) : ?>
                    <a <?php if(preg_match("/^#/", $lien)): ?>data-toggle="modal"<?php endif; ?>
                        href="<?php echo $lien ?>"
                        class="btn <?php echo getClassButtonEtatDRMCalendrier($isTeledeclarationMode, $calendrier, $periode, $etb); ?> btn-block <?php echo (!$multiEtablissement && $lastDrmToCompleteAndToStart->getRawValue()->periode == $periode)? ' to_autofocus ' : ''; ?>
                        "
                    >
                        <?php echo getEtatDRMLibelleCalendrier($isTeledeclarationMode, $calendrier, $periode, $etb, false); ?>
                    </a>
                    <?php else: ?>
                        <i class="btn disabled btn-block"><?php echo getEtatDRMLibelleCalendrier($isTeledeclarationMode, $calendrier, $periode, $etb, false); ?></i>
                    <?php endif; ?>
                </div>
            </div>
            <?php if($multiEtablissement): ?>
                <a <?php if(preg_match("/^#/", $lien)): ?>data-toggle="modal"<?php endif; ?>
                    href="<?php echo $lien ?>" data-template='<div style="margin-top: 10px !important;" class="popover" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>' data-placement="top" data-content="#calendrier_item_<?php echo $periode ?>_<?php echo $etb->identifiant ?>" class="toggle-popover btn btn-lg  <?php echo getClassButtonEtatDRMCalendrier($isTeledeclarationMode, $calendrier, $periode, $etb, true); ?>"><?php echo getEtatDRMLibelleCalendrier($isTeledeclarationMode, $calendrier, $periode, $etb, true); ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

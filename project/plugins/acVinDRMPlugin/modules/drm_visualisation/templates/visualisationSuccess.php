<?php use_helper("Date"); ?>
<?php use_helper('DRM'); ?>

<?php include_partial('drm/breadcrumb', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<?php if (!$isTeledeclarationMode): ?>
<div class="row" style="opacity: 0.7">
    <div class="col-xs-12">
         <?php include_component('drm', 'formEtablissementChoice', array('identifiant' => $drm->etablissement->_id, 'autofocus' => true)) ?>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-xs-12">


        <h3 style="margin-bottom: 30px">DRM <?php echo getFrPeriodeElision($drm->periode); ?>
            <small>
                <?php if ($drm->isTeledeclare()): ?>
                    (Validée le <?php echo format_date($drm->valide->date_signee, "dd/MM/yyyy", "fr_FR"); ?>)
                <?php elseif ($drm->isImport()): ?>
                    (Importée le <?php echo format_date($drm->valide->date_signee, "dd/MM/yyyy", "fr_FR"); ?>)
                <?php else: ?>
                    (Saisie interne le <?php echo format_date($drm->valide->date_saisie, "dd/MM/yyyy", "fr_FR"); ?>)
                <?php endif; ?>
            </small>
        <?php if (!$isTeledeclarationMode || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?>
            <div class="pull-right">
                <?php if ($drm->isReouvrable()): ?>
                    <a class="btn btn-warning" href="<?php echo url_for('drm_reouvrir', $drm) ?>">Ré-ouvrir la DRM</a>
                <?php elseif($drm->isModifiable() && $drm->isTeledeclare()): ?>
                    <a class="btn btn-warning" href="<?php echo url_for('drm_modificative', $drm) ?>">Modificatrice de la DRM</a>
                <?php elseif($drm->isModifiable()): ?>
                    <a class="btn btn-warning" href="<?php echo url_for('drm_modificative', $drm) ?>">Modifier la DRM</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </h3>


        <!--<div id="drm_validation_coordonnees">
            <div class="drm_validation_societe">
                <?php //include_partial('drm_visualisation/societe_infos', array('drm' => $drm, 'isModifiable' => false)); ?>
            </div>
            <div class="drm_validation_etablissement">
                <?php //include_partial('drm_visualisation/etablissement_infos', array('drm' => $drm, 'isModifiable' => false)); ?>
            </div>
        </div>-->

        <?php if (!$isTeledeclarationMode || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?>
            <?php if ($drm_suivante && $drm_suivante->isRectificative() && !$drm_suivante->isValidee()):
                ?>
                <div class="vigilance_list">
                    <ul>
                        <li><?php echo MessagesClient::getInstance()->getMessage('msg_rectificatif_suivante') ?></li>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$drm->isMaster()): ?>
                <div class="alert alert-warning">
                    Ce n'est pas la <a href="<?php echo ($drm->getMaster()->isValidee())? url_for('drm_visualisation', $drm->getMaster()) :  url_for('drm_redirect_etape', $drm->getMaster()) ?>">dernière version</a> de la DRM, le tableau récapitulatif des stocks n'est donc pas à jour.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($drm->isNegoce()): ?>
            <div class="alert alert-info">
				        <?php if($isTeledeclarationMode): ?>
                  <img src="/images/visuels/prodouane.png" />
                  <p><br />Vous pouvez à présent télécharger votre DRM au format XML afin de l'importer en DTI+ sur le site prodouanes via le lien suivant : <a href="https://pro.douane.gouv.fr/">pro.douane.gouv.fr</a><br />
                <?php else: ?>
                  <p><br />Ceci est une Drm Négoce : téléchargement de la DRM au format XML : <a href="https://pro.douane.gouv.fr/">pro.douane.gouv.fr</a><br />
                <?php endif; ?>
                <a class="pull-right btn btn-default" download="<?= $drm->_id ?>.xml" target="_blank" href="<?php echo url_for('drm_xml', $drm); ?>">Télécharger le XML</a><br />&nbsp;</p>
            </div>
		<?php endif; ?>

        <?php include_partial('drm_visualisation/recap_stocks_mouvements', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'no_link' => $no_link, 'mouvementsByProduit' => $mouvementsByProduit, 'visualisation' => true, 'typeDetailKey' => DRM::DETAILS_KEY_SUSPENDU, 'typeKey' => DRMClient::TYPE_DRM_SUSPENDU)) ?>
        <?php include_partial('drm_visualisation/recap_stocks_mouvements', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'no_link' => $no_link, 'mouvementsByProduit' => $mouvementsByProduit, 'visualisation' => true, 'typeDetailKey' => DRM::DETAILS_KEY_ACQUITTE, 'typeKey' => DRMClient::TYPE_DRM_ACQUITTE)) ?>


    </div>
</div>

<?php include_partial('drm_visualisation/reserveinterpro', array('drm' => $drm)) ?>

<?php if ((!$isTeledeclarationMode  || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())) && $drm->commentaire): ?>
    <div class="row">
        <div class="col-xs-12">
            <h4>Commentaire interne</h4>
        </div>
        <div class="col-xs-12">
            <div class="well">
                <?php echo nl2br($drm->commentaire); ?>
            </div>
        </div>
    </div>
    <br/>
<?php else: ?>
    <?php include_partial('drm_visualisation/recap_crds', array('drm' => $drm)) ?>
    <?php include_partial('drm_visualisation/recapAnnexes', array('drm' => $drm)) ?>
<?php endif; ?>



<?php include_partial('drm_visualisation/recapCsv', array('drm' => $drm)) ?>
<?php include_partial('drm_visualisation/recapDroits', array('drm' => $drm, 'recapCvos' => $recapCvos, 'isTeledeclarationMode' => $isTeledeclarationMode)) ?>

<br/><br/>
<?php include_partial('drm_visualisation/douane_table', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
<?php include_partial('drm_xml/rapport_retour', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<br/><br/>
<div class="row">
    <div class="col-xs-4">
        <a href="<?php echo url_for('drm_etablissement', array('identifiant' => $drm->identifiant)); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour à mon espace DRM</a>
    </div>
    <div class="col-xs-4 text-center">
      <?php echo getPointAideHtml('drm','visualisation_pdf'); ?>
        <a href="<?php echo url_for('drm_pdf', array('identifiant' => $drm->getIdentifiant(), 'periode_version' => $drm->getPeriodeAndVersion(), 'appellation' => 0)); ?>" class="btn btn-success">Télécharger le PDF</a>
    </div>
    <div class="col-xs-4 text-right">
      <?php if ((!$isTeledeclarationMode || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())) && $drm->isNegoce()): ?>
        <a class="btn btn-warning btn-xs pull-right" href="<?php echo url_for('drm_edition_libelles', $drm) ?>">Modifier les libellés prodouane</a>
      <?php endif; ?>
    <?php if(isset($compte) && $compte && $compte->hasDroit(Roles::TELEDECLARATION_DOUANE) && $isTeledeclarationMode && !$drm->isNegoce()): ?>
      <?php if (!$drm->transmission_douane->success) : ?>
        <a style="margin-left: 5px;" href="<?php echo url_for('drm_transmission', $drm); ?>" class="btn btn-success" ><span>Transmettre la Drm sur CIEL</span></a>
      <?php else: ?>
        <a style="margin-left: 5px;" href="https://pro.douane.gouv.fr/" class="btn btn-success" ><span>Se rendre sur Pro Dou@ne</span></a>
      <?php endif; ?>
    <?php endif; ?>
    </div>
</div>

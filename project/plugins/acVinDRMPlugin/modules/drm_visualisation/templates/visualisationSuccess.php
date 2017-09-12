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


        <h3 style="margin-bottom: 30px">DRM <?php echo getFrPeriodeElision($drm->periode); ?> <?php if ($drm->isTeledeclare()): ?><small>(Validée le <?php echo format_date($drm->valide->date_signee, "dd/MM/yyyy", "fr_FR"); ?>)</small><?php endif; ?>
        <?php if (!$isTeledeclarationMode): ?>
          <?php if ($drm->isModifiable()): ?>
            <div class="pull-right">
              <a class="btn btn-warning" href="<?php echo url_for('drm_modificative', $drm) ?>">Modifier la DRM</a>
            </div>
          <?php endif; ?>
          <?php if ($drm->isTeledeclareFacturee()): ?>
              <div class="pull-right">
                  <a class="btn btn-warning" href="<?php echo url_for('drm_modificative', $drm) ?>">Modificatrice de la DRM</a>
              </div>
          <?php endif; ?>

          <?php if ($drm->isTeledeclareNonFacturee()): ?>
              <div class="pull-right">
                  <a class="btn btn-warning" href="<?php echo url_for('drm_reouvrir', $drm) ?>">Ré-ouvrir la DRM</a>
              </div>
          <?php endif; ?>
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

        <?php if (!$isTeledeclarationMode): ?>
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
                    Ce n'est pas la <a href="<?php echo url_for('drm_visualisation', $drm->getMaster()) ?>">dernière version</a> de la DRM, le tableau récapitulatif des stocks n'est donc pas à jour.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php include_partial('drm_visualisation/recap_stocks_mouvements', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'no_link' => $no_link, 'mouvementsByProduit' => $mouvementsByProduit, 'visualisation' => true, 'typeDetailKey' => DRM::DETAILS_KEY_SUSPENDU, 'typeKey' => DRMClient::TYPE_DRM_SUSPENDU)) ?>
        <?php include_partial('drm_visualisation/recap_stocks_mouvements', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'no_link' => $no_link, 'mouvementsByProduit' => $mouvementsByProduit, 'visualisation' => true, 'typeDetailKey' => DRM::DETAILS_KEY_ACQUITTE, 'typeKey' => DRMClient::TYPE_DRM_ACQUITTE)) ?>
    </div>
</div>

<?php if (!$isTeledeclarationMode && $drm->commentaire): ?>
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
<?php include_partial('drm_visualisation/recapDroits', array('drm' => $drm, 'recapCvos' => $recapCvos, 'isTeledeclarationMode' => $isTeledeclarationMode)) ?>
<?php if ($drm->exist('transmission_douane') && $drm->transmission_douane): ?>
<div class="row">
  <div class="col-xs-12">
    <h3>Transmission sur le portail proDou@ane <small>(<?php echo link_to('xml', 'drm_xml', $drm); ?>)</small></h3>
    <?php if ($drm->transmission_douane->success) : ?>
      <p>La transmission a été réalisée avec succès le <?php echo $drm->getTransmissionDate(); ?> avec l'accusé reception numéro <?php echo $drm->transmission_douane->id_declaration ?>.</p>
    <?php else: ?>
      <p>La transmission a échouée. Le message d'erreur envoyé par le portail des douanes est « <?php echo $drm->getTransmissionErreur(); ?> ».</p>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
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
    <?php if ($drm->isTeledeclare() && !$isTeledeclarationMode) : ?>
          <?php if ($drm->isNonFactures()): ?>
          <a href="<?php echo url_for('drm_reopen', $drm); ?>" class="btn btn-warning">Reouvrir la DRM</a>
          <?php else: ?>
          <span>DRM Facturée (pas réouvrable)</span>
          <?php endif; ?>
    <?php endif; ?>
    <?php if(isset($compte) && $compte && $compte->hasDroit("teledeclaration_douane") && $isTeledeclarationMode): ?>
      <?php if (!$drm->transmission_douane->success) : ?>
        <a style="margin-left: 5px;" href="<?php echo url_for('drm_transmission', $drm); ?>" class="btn btn-success" ><span>Transmettre la Drm sur CIEL</span></a>
      <?php else: ?>
        <a style="margin-left: 5px;" href="https://pro.douane.gouv.fr/" class="btn btn-success" ><span>Se rendre sur Pro Dou@ne</span></a>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

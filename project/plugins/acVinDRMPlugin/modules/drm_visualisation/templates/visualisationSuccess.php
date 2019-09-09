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
        <?php if (!$isTeledeclarationMode || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?>
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
        
        <?php if ($drm->isNegoce() && $isTeledeclarationMode): ?>
            <div class="alert alert-info">
				<img src="/images/visuels/prodouane.png" />
                <p><br />Vous pouvez à présent télécharger votre DRM au format XML afin de l'importer en DTI+ sur le site prodouanes via le lien suivant : <a href="https://pro.douane.gouv.fr/">pro.douane.gouv.fr</a><br />
                <a class="pull-right btn btn-default" download="<?= $drm->_id ?>.xml" target="_blank" href="<?php echo url_for('drm_xml', $drm); ?>">Télécharger le XML</a><br />&nbsp;</p>
            </div>
		<?php endif; ?>

        <?php include_partial('drm_visualisation/recap_stocks_mouvements', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'no_link' => $no_link, 'mouvementsByProduit' => $mouvementsByProduit, 'visualisation' => true, 'typeDetailKey' => DRM::DETAILS_KEY_SUSPENDU, 'typeKey' => DRMClient::TYPE_DRM_SUSPENDU)) ?>
        <?php include_partial('drm_visualisation/recap_stocks_mouvements', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'no_link' => $no_link, 'mouvementsByProduit' => $mouvementsByProduit, 'visualisation' => true, 'typeDetailKey' => DRM::DETAILS_KEY_ACQUITTE, 'typeKey' => DRMClient::TYPE_DRM_ACQUITTE)) ?>


    </div>
</div>

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
<?php include_partial('drm_visualisation/recapDroits', array('drm' => $drm, 'recapCvos' => $recapCvos, 'isTeledeclarationMode' => $isTeledeclarationMode)) ?>
<?php if ($drm->exist('transmission_douane') && $drm->transmission_douane): ?>
<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title text-center"<?php if (!$isTeledeclarationMode || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?> style="padding: 0 0 5px 0;"<?php endif; ?>>Transmission proDou@ane <?php if (!$isTeledeclarationMode || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?><a class="btn btn-warning btn-xs pull-right" href="<?php echo url_for('drm_edition_libelles', $drm) ?>">Modifier les libellés prodouane</a><?php endif; ?></h3>
        </div>
        <div class="panel-content>">
            <table class="table table-striped table-condensed">
                <tbody>
                  <tr>
                      <td class="col-xs-4">Transmission (<?php echo link_to('XML transmis', 'drm_xml_table', array("identifiant" => $drm->identifiant,"periode_version" => $drm->getPeriodeAndVersion(), "retour" => "0")); ?>)</td>
                      <td class="col-xs-8">
                        <?php if ($drm->transmission_douane->success) : ?>
                          La transmission a été réalisée avec succès le <?php echo $drm->getTransmissionDate(); ?> avec l'accusé reception numéro <?php echo $drm->transmission_douane->id_declaration ?>.
                        <?php elseif (preg_match('/HTTP Error 0/', $drm->getTransmissionErreur())) : ?>
                          Le service de reception des DRM de la Douane n'était disponible au moment de la transmission. Lorsque la liaison sera de nouveau disponible, une retransmission sera effectuée.
                        <?php else: ?>
                          La transmission a échouée. Le(s) message(s) d'erreur envoyé(s) par le portail des douanes sont :
                          <ul>
                            <?php foreach(explode('.', $drm->getTransmissionErreur()) as $li) if ($li) : ?>
                              <li> «&nbsp;<?php echo $li ?>&nbsp;» </li>
                            <?php endif; ?>
                          </ul>
                        <?php endif; ?>
                        <?php if (!$isTeledeclarationMode  || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?>
                          <a id="retransmission" data-link="<?php echo url_for('drm_retransmission', $drm); ?>" class="pull-right btn btn-xs btn-default" >retransmettre&nbsp;<span class="glyphicon glyphicon-repeat"></span></a>
                        <?php endif; ?>
                      </td>
                  </tr>
                  <?php if (!$isTeledeclarationMode  || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?>
                    <?php if (is_null($drm->transmission_douane->coherente)) : ?>
                      <tr><td>Retour XML</td><td>Aucun retour de la part de proDou@ne n'a été effectué<a href="<?php echo url_for('drm_retour_refresh', $drm); ?>"  class="pull-right btn btn-xs btn-default" ><span class="glyphicon glyphicon-repeat"></span></a></td></tr>
                    <?php elseif($drm->transmission_douane->coherente): ?>
                      <tr><td>Retour XML (<a href="<?php echo url_for('drm_xml_table', array('identifiant' => $drm->identifiant,"periode_version" => $drm->getPeriodeAndVersion(), 'retour' => "1")); ?>">XML reçu</a>)</td><td>La DRM est <strong>conforme</strong> à celle de proDou@ne
                              <a href="<?php echo url_for('drm_retour_refresh', $drm); ?>"  class="pull-right btn btn-xs btn-default" ><span class="glyphicon glyphicon-repeat"></span></a>
                          </td>
                      </tr>
                    <?php else: ?>
                      <tr>
                          <td>Retour XML (<a href="<?php echo url_for('drm_xml_table', array('identifiant' => $drm->identifiant,"periode_version" => $drm->getPeriodeAndVersion(), 'retour' => "1")); ?>">XML reçu</a>)</td><td>La DRM n'est <strong>pas conforme</strong> à celle de proDou@ne
                              <a href="<?php echo url_for('drm_retour_refresh', $drm); ?>"  class="pull-right btn btn-xs btn-default" ><span class="glyphicon glyphicon-repeat"></span></a>
                          </td>
                      </tr>
                    <?php endif; ?>
                  <?php endif; ?>
                </tbody>
              </table>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
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
    <?php if(isset($compte) && $compte && $compte->hasDroit(Roles::TELEDECLARATION_DOUANE) && $isTeledeclarationMode && !$drm->isNegoce()): ?>
      <?php if (!$drm->transmission_douane->success) : ?>
        <a style="margin-left: 5px;" href="<?php echo url_for('drm_transmission', $drm); ?>" class="btn btn-success" ><span>Transmettre la Drm sur CIEL</span></a>
      <?php else: ?>
        <a style="margin-left: 5px;" href="https://pro.douane.gouv.fr/" class="btn btn-success" ><span>Se rendre sur Pro Dou@ne</span></a>
      <?php endif; ?>
    <?php endif; ?>
    </div>
</div>

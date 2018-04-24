<?php use_helper("Date"); ?>
<?php use_helper('DRM'); ?>
<?php use_helper('PointsAides'); ?>
<!-- #principal -->

<?php include_partial('drm/breadcrumb', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<section id="principal" class="drm">

<?php include_partial('drm/etapes', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'etape_courante' => DRMClient::ETAPE_VALIDATION)); ?>

<form action="<?php echo url_for('drm_validation', $form->getObject()) ?>" method="post" id="drm_validation">
    <div class="row">
        <div class="col-xs-12">
            <p><?php echo getPointAideText('drm','etape_recap_description'); ?></p>
            <?php if ($isTeledeclarationMode): ?>
                <?php //include_partial('drm_validation/coordonnees_operateurs', array('drm' => $drm, 'validationCoordonneesSocieteForm' => $validationCoordonneesSocieteForm, 'validationCoordonneesEtablissementForm' => $validationCoordonneesEtablissementForm)); ?>
            <?php endif; ?>

            <?php include_partial('drm_visualisation/recap_stocks_mouvements', array('drm' => $drm, 'mouvements' => $mouvements, 'no_link' => $no_link, 'isTeledeclarationMode' => $isTeledeclarationMode, 'visualisation' => false, 'mouvementsByProduit' => $mouvementsByProduit, 'typeDetailKey' => DRM::DETAILS_KEY_SUSPENDU, 'typeKey' => DRMClient::TYPE_DRM_SUSPENDU)); ?>
            <?php include_partial('drm_visualisation/recap_stocks_mouvements', array('drm' => $drm, 'mouvements' => $mouvements, 'no_link' => $no_link, 'isTeledeclarationMode' => $isTeledeclarationMode, 'visualisation' => false, 'mouvementsByProduit' => $mouvementsByProduit, 'typeDetailKey' => DRM::DETAILS_KEY_ACQUITTE, 'typeKey' => DRMClient::TYPE_DRM_ACQUITTE)); ?>

			
      		<?php if (($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN) || $isUsurpationMode) && $sf_user->hasCredential(Roles::TELEDECLARATION_DOUANE) && $sf_user->getCompte()->hasDroit("teledeclaration_douane")): ?>
            <a class="btn btn-warning pull-right" href="<?php echo url_for('drm_edition_libelles', $drm) ?>">Modifier les libellés produits</a>
            <?php endif; ?>
            
            <?php if ($isTeledeclarationMode): ?>
                <?php include_partial('drm_visualisation/recap_crds', array('drm' => $drm)); ?>
                <?php include_partial('drm_visualisation/recapAnnexes', array('drm' => $drm)) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <?php echo $form->renderHiddenFields(); ?>
                <?php if (!$isTeledeclarationMode): ?>
                    <?php echo $form['commentaire']->renderLabel(); ?>
                    <?php echo $form['commentaire']->renderError(); ?>
                    <?php echo $form['commentaire']->render(array('tabindex'=>"30")); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
          <?php if($validation->hasErreurs()): ?>
          <div class="alert alert-danger">
              <strong>Points bloquants</strong><?php echo getPointAideHtml('drm','recapitulatif_pt_bloquant'); ?>
              <?php include_partial('document_validation/validationType', array('points' => $validation->getPoints('erreur'))) ?>
          </div>
          <?php endif; ?>

          <?php if($validation->hasVigilances()): ?>
          <div class="alert alert-warning">
              <strong>Points de vigilance</strong><?php echo getPointAideHtml('drm','recapitulatif_pt_vigilance'); ?>
              <?php include_partial('document_validation/validationType', array('points' => $validation->getPoints('vigilance'))) ?>
          </div>
          <?php endif; ?>
        </div>
    </div>

    <?php if (!$isTeledeclarationMode && $drm->exist('transmission_douane') && $drm->transmission_douane->coherente === false): ?>
    <div class="row">
      <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title text-center">Rapport du retour XML proDou@ane</h3>
            </div>
            <div class="panel-content>">
                <table class="table table-striped table-condensed">
                    <tbody>
                      <tr>
                        <td colspan="3">Identification du problème rencontré (<a href="<?php echo url_for('drm_retour', $drm); ?>">XML reçu</a>)</td>
                        <td>Valeur proDou@ne</td>
                        <th>Valeur Interpro</th>
                      </tr>
                      <?php foreach ($drm->getXMLComparison()->getFormattedXMLComparaison() as $problemeSrc => $values): ?>
                          <?php preg_match('/^(\[.+\]) (.+)((entree|sortie|stock).+)$/', $problemeSrc, $matches); ?>
                        <tr>
                          <td><?php echo $matches[1]; ?></td>
                          <td><?php echo $matches[2]; ?></td>
                          <td><?php echo $matches[3]; ?></td>
                          <td><?php echo $values[0]; ?></td>
                          <td><?php echo $values[1]; ?></td></tr>
                      <?php endforeach; ?>
                    </tbody>
                </table>

          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xs-4 text-left">
            <a tabindex="-1" href="<?php echo ($isTeledeclarationMode) ? url_for('drm_annexes', $drm) : url_for('drm_edition_details', array('sf_subject' => $drm, 'details' => DRM::DETAILS_KEY_SUSPENDU)); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Etape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
            <?php /*if ($isTeledeclarationMode) : ?>
                <a tabindex="-1" class="btn btn-danger" href="<?php echo url_for('drm_etablissement', $vrac); ?>">Supprimer le brouillon</a>
            <?php endif;*/ ?>
                <?php echo getPointAideHtml('drm','visualisation_pdf'); ?>
                <a href="<?php echo url_for('drm_pdf', array('identifiant' => $drm->getIdentifiant(), 'periode_version' => $drm->getPeriodeAndVersion(), 'appellation' => 0)); ?>" class="btn btn-success">Télécharger le PDF</a>

        </div>
        <div class="col-xs-4 text-right">
                <?php if ($isTeledeclarationMode): ?>
                        <?php echo $form['email_transmission']->render(); ?>
                        <?php if($compte->hasDroit(Roles::TELEDECLARATION_DOUANE)): ?>
                              <?php echo $form['transmission_ciel']->render(); ?>
                        <?php endif; ?>
                        <button <?php if (!$validation->isValide()) : ?>disabled="disabled"<?php endif; ?> type="button" data-toggle="modal" data-target="#signature_drm_popup" <?php if (!$validation->isValide()): ?>disabled="disabled"<?php endif; ?> href="#signature_drm_popup_content" class="btn btn-success"><span>Valider</span></button>

                <?php else: ?>
                        <button <?php if (!$validation->isValide()) : ?>disabled="disabled"<?php endif; ?>class="btn btn-success" type="submit" tabindex="40" >Terminer la saisie <span class="glyphicon glyphicon-ok"></span></button>
                <?php endif; ?>
        </div>
    </div>
    <?php if ($isTeledeclarationMode): ?>
        <?php include_partial('drm_validation/signature_popup', array('drm' => $drm, 'societe' => $societe, 'compte' => $compte, 'etablissementPrincipal' => $etablissementPrincipal, 'validationForm' => $form)); ?>
    <?php endif; ?>
</form>
</div>

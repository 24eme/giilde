<?php use_helper("Date"); ?>
<?php use_helper('DRM'); ?>
<?php use_helper('PointsAides'); ?>
<!-- #principal -->

<?php include_partial('drm/breadcrumb', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<section id="principal" class="drm">

    <?php include_partial('drm/etapes', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'etape_courante' => DRMClient::ETAPE_SAISIE."_".$detailsKey)); ?>
    <?php include_partial('drm/controlMessage'); ?>

    <div id="application_drm">
        <?php if($detailsKey == "details"): ?>
          <p><?php echo getPointAideText('drm','etape_mvt_supendus_description'); ?></p>
        <?php else: ?>
          <p><?php echo getPointAideText('drm','etape_mvt_acquittes_description'); ?></p>
        <?php endif; ?>
        <div class="row">
            <div class="col-sm-9" id="contenu_onglet">
                <?php
                include_partial('drm_edition/list', array('drm_noeud' => $drm->declaration,
                    'config' => $config,
                    'detail' => $detail,
                    'detailsKey' => $detailsKey,
                    'produits' => $details,
                    'drm' => $drm,
                    'etablissement' => $etablissementPrincipal,
                    'formFavoris' => $formFavoris,
                    'form' => $form,
                    'favoris' => $favoris,
                    'isTeledeclarationMode' => $isTeledeclarationMode,
                    'detailsNodes' => $detailsNodes,
                  'saisieSuspendu' => $saisieSuspendu));
                ?>
            </div>
            <div class="col-sm-3">
                <div class="panel panel-default stickyHeader">
                    <div class="panel-heading">Édition des Produits<?php echo getPointAideHtml('drm','mouvements_choix_produits') ?></div>
                      <?php if(!$isTeledeclarationMode): ?>
                        <div class="panel-body">
                        <?php include_component('drm_edition', 'produitForm', array('drm' => $drm, 'config' => $config, 'detailsKey' => $detailsKey)) ?>
                      </div>
                      <?php endif; ?>
                    <ul id="list-produits" class="list-group drm_fil_edition_produit pointer">
                        <?php foreach ($details as $produit) : ?>
                            <?php
                            if (!$produit->hasMovements()): continue;
                            endif;
                            ?>
                            <a title="<?php echo $produit->getLibelle("%format_libelle%") ?>" style="position: relative;  overflow:hidden; cursor:pointer;
  display: block; height: 30px; padding: 5px 15px;
  margin-bottom: -1px;" data-hash="<?php echo $produit->getHash() ?>" <?php echo (!$produit->hasMovements()) ? 'style="display:none;"' : '' ?> class="list-group-item <?php echo ($produit->isEdited()) ? 'edited list-group-item-success' : '' ?>">

                                <small><?php echo str_replace("Alsace Grand Cru","Gd Cru",$produit->getLibelle("%format_libelle%")); ?></small>
                                <span style="position:absolute; right: 1px; top:5px;" class="btn btn-xs btn-link glyphicon glyphicon-eye-open "></span>
                            </a>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-9">
                <div id="navigation_etapes" class="row">
                    <div class="col-xs-3 text-left">
                        <?php if($detailsKey == DRM::DETAILS_KEY_ACQUITTE && $drm->isDouaneType(DRMClient::TYPE_DRM_SUSPENDU)): ?>
                            <a tabindex="-1" href="<?php echo url_for('drm_edition_details', array('sf_subject' => $drm, 'details' => DRM::DETAILS_KEY_SUSPENDU)); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Etape précédente</a>
                        <?php else: ?>
                            <a tabindex="-1" href="<?php echo ($isTeledeclarationMode) ? url_for('drm_choix_produit', $drm) :   url_for('drm_etablissement', $drm); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Etape précédente</a>
                        <?php endif; ?>
                    </div>
                    <div class="col-xs-6 text-center">
                        <?php if (!$isTeledeclarationMode): ?>
                            <a href="<?php echo url_for('drm_etablissement', $drm->getEtablissement()); ?>" class="btn btn-default">Enregistrer en brouillon</a>
                        <?php endif; ?>
                        <a class="btn btn-default" data-toggle="modal" data-target="#drm_delete_popup" >Supprimer la DRM</a>
                    </div>
                    <div class="col-xs-3 text-right">
                        <form action="<?php echo url_for('drm_edition_details', array('sf_subject' => $formValidation->getObject(), 'details' => $detailsKey)) ?>" method="post">
        <?php echo $formValidation->renderHiddenFields(); ?>
                            <button type="submit" class="btn btn-success">Étape suivante <span class="glyphicon glyphicon-chevron-right"></span></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
include_partial('drm/colonne_droite', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode));

include_partial('drm/deleteDrmPopup', array('drm' => $drm, 'deleteForm' => $deleteForm));
?>

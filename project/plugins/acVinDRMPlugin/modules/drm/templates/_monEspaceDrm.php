<?php use_helper('Date'); ?>
<?php use_helper('DRM'); ?>
<?php use_helper('Orthographe'); ?>
<?php use_helper('PointsAides'); ?>
                <ul style="<?php if (!isset($btnAccess)): ?>height: auto<?php endif; ?>" class="list-group">
                    <?php foreach ($lastDrmToCompleteAndToStart as $etb => $drmsByEtb) : ?>

                                    <?php if ($drmsByEtb->statut == DRMCalendrier::STATUT_EN_COURS): ?>
                                        <li class="list-group-item">
                                          <div class="row">
                                              <div class="col-xs-12">
                                              <h4><?php echo $drmsByEtb->nom . ' (' . $etb . ')'; ?></h4>
                                            </div>
                                              </div>
                                             <div class="row">
                                                 <div class="col-xs-12">
                                            <a href="<?php echo url_for('drm_redirect_etape', $drmsByEtb->drm); ?>" ><span>Finir la DRM <?php echo getFrPeriodeElision($drmsByEtb->periode); ?></span></a><?php echo getPointAideHtml('drm','menu_list') ?>
                                          </div>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($drmsByEtb->statut == DRMCalendrier::STATUT_EN_COURS_NON_TELEDECLARE): ?>
                                        <li class="list-group-item text-muted">
                                          <div class="row">
                                              <div class="col-xs-12">
                                              <h4><?php echo $drmsByEtb->nom . ' (' . $etb . ')'; ?></h4>
                                            </div> </div>
                                             <div class="row">
                                                 <div class="col-xs-12">
                                            <span>La DRM <?php echo getFrPeriodeElision($drmsByEtb->periode); ?> est en cours de saisie à l'interprofession</span>
                                           </div> </div>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($drmsByEtb->statut == DRMCalendrier::STATUT_NOUVELLE): ?>
                                        <?php
                                        $lienNouvelle = url_for('drm_nouvelle', array('identifiant' => $etb, 'periode' => $drmsByEtb->periode));
                                        if ($isTeledeclarationMode) {
                                          //  $lienNouvelle = url_for('drm_etablissement', array('identifiant' => $etb)) .
                                               $lienNouvelle = '#drm_nouvelle_' . $drmsByEtb->periode . '_' . $etb;
                                            if (!$hasNoPopupCreation) {
                                                include_partial('drm/creationDrmPopup', array('periode' => $drmsByEtb->periode, 'identifiant' => $etb, 'drmCreationForm' => $drmsToCreateForms[$etb . '_' . $drmsByEtb->periode]));
                                            }
                                        }
                                        ?>
                                        <li class="list-group-item">
                                          <div class="row">
                                              <div class="col-xs-12">
                                              <h4><?php echo $drmsByEtb->nom . ' - ' . $drmsByEtb->no_accises . ' (' . $etb . ')'; ?></h4>
                                            </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12">
                                              <a style="cursor:pointer;" data-toggle="modal" data-target="<?php echo $lienNouvelle; ?>" class="<?php echo ($isTeledeclarationMode) ? 'drm_nouvelle_teledeclaration' : '' ?>"><span>Créer la DRM <?php echo getFrPeriodeElision($drmsByEtb->periode); ?></span></a>

                                            </div>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($drmsByEtb->statut == DRMCalendrier::STATUT_VALIDEE): ?>
                                        <li class="list-group-item text-success">
                                           <div class="row">
                                               <div class="col-xs-12">
                                              <h4><?php echo $drmsByEtb->nom . ' (' . $etb . ')'; ?></h4>
                                            </div></div>
                                            <div class="row">
                                                <div class="col-xs-12">
                                            <a href="<?php echo url_for('drm_visualisation', $drmsByEtb->drm); ?>"> <span>Visualiser votre DRM <?php echo getFrPeriodeElision($drmsByEtb->periode); ?>
                                                </span></a>
                                              </div></div>
                                        </li>
                                      <?php endif; ?>
                <?php endforeach; ?>
                </ul>

<?php use_helper("Date"); ?>
<?php use_helper('DRM'); ?>

<?php use_helper('PointsAides'); ?>

<?php
$paiement_douane_frequence = ($societe->exist('paiement_douane_frequence')) ? $societe->paiement_douane_frequence : null;
?>
<?php include_partial('drm/breadcrumb', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<!-- #principal -->
<section id="principal" class="drm">

    <?php include_partial('drm/etapes', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'etape_courante' => DRMClient::ETAPE_ADMINISTRATION)); ?>
    <?php include_partial('drm/controlMessage'); ?>
    <div id="application_drm">
        <p><?php echo getPointAideText('drm','etape_annexes_description'); ?></p>
        <form action="<?php echo url_for('drm_annexes', $annexesForm->getObject()); ?>" class="form-horizontal" method="post">
          <?php echo $annexesForm->renderGlobalErrors(); ?>
          <?php echo $annexesForm->renderHiddenFields(); ?>
            <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-default" >
                        <div class="panel-heading " style="cursor:pointer;" id="drm_annexes_documents" >
                          <div class="row" data-toggle="collapse" data-parent="#accordion" href="#collapse_documents" aria-expanded="true" aria-controls="collapse_documents">
                            <div class="col-xs-11">
                              <h3 class="panel-title text-center"><strong>Déclaration des documents d'accompagnement</strong></h3>
                            </div>
                            <div class="col-xs-1 text-right">
                              <a role="button" >
                                &nbsp;<span class="glyphicon<?php echo (1 || $drm->hasAnnexes())? ' glyphicon-chevron-down' : ' glyphicon-chevron-right' ?>" style="padding-top: 4px;" ></span>
                              </a>
                            </div>
                          </div>
                        </div>
                        <div id="collapse_documents" class="panel-collapse collapse<?php echo (1 || $drm->hasAnnexes())? ' in' : '' ?>" role="tabpanel" aria-labelledby="drm_annexes_documents">
                          <div class="panel-body">
                            <p><?php echo getPointAideText('drm','annexe_document_accompagnement'); ?></p>
                            <table id="table_drm_adminitration" class="table table-bordered table-striped">
                                <thead >
                                    <tr>
                                        <th class="col-xs-4" >Type de document</th>
                                        <th class="col-xs-4">Numéro de début</th>
                                        <th class="col-xs-4">Numéro de fin</th>
                                        <th class="col-xs-4">Nombre de documents</th>
                                    </tr>
                                </thead>
                                <tbody class="drm_adminitration">
                                        <tr>
                                          <td style="vertical-align: middle;" class="drm_annexes_type">DAE</td>
                                          <td colspan="3" style="text-align: center;"><b>Déclaration non nécessaire pour les documents électroniques réalisés sous Gamma</b></td>
                                      </tr>
                                    <?php foreach ($annexesForm->getDocTypes() as $typeDoc): ?>
                                        <tr>
                                            <td style="vertical-align: middle;" class="drm_annexes_type"><?php echo DRMClient::$drm_documents_daccompagnement[$typeDoc]; ?><?php echo getPointAideHtml('drm','annexe_'.strtolower($typeDoc)) ?></td>
                                            <td class="drm_annexes_doc_debut"><?php echo $annexesForm[$typeDoc . '_debut']->render(); ?></td>
                                            <td class="drm_annexes_doc_fin"><?php echo $annexesForm[$typeDoc . '_fin']->render(); ?></td>
                                            <td class="drm_annexes_doc_nb"><?php echo $annexesForm[$typeDoc . '_nb']->render(); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                          </div>
                        </div>
                    </div>
                </div>
              </div>

              <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="cursor:pointer;" id="drm_annexes_apurement" data-toggle="collapse" data-parent="#accordion" href="#collapse_apurement" aria-expanded="true" aria-controls="collapse_apurement" >
                          <div class="row">
                            <div class="col-xs-11">
                              <h3 class="panel-title text-center"><strong>Relevé de non apurement</strong></h3>
                            </div>
                            <div class="col-xs-1 text-right">
                              <a role="button">
                                &nbsp;<span class="glyphicon <?php echo ($drm->hasReleveNonApurement())? ' glyphicon-chevron-down' : ' glyphicon-chevron-right' ?>" style="padding-top: 4px;" ></span>
                              </a>
                            </div>
                          </div>
                        </div>
                        <div id="collapse_apurement" class="panel-collapse collapse<?php echo ($drm->hasReleveNonApurement())? ' in' : '' ?>" role="tabpanel" aria-labelledby="drm_annexes_apurement">
                        <div class="panel-body">
                        <p><?php echo getPointAideText('drm','annexe_nonapurement'); ?><p/>
                          <table id="table_drm_non_apurement" class="table table-bordered table-striped">
                              <thead >
                                  <tr>
                                      <th class="col-xs-4">Numéro de document<?php echo getPointAideHtml('drm','annexe_nonapurement_num_doc'); ?></th>
                                      <th class="drm_non_apurement_date_emission col-xs-4">Date d'expédition<?php echo getPointAideHtml('drm','annexe_nonapurement_date'); ?></th>
                                      <th class="col-xs-4">Numéro d'accise<?php echo getPointAideHtml('drm','annexe_nonapurement_num_accise'); ?></th>
                                      <th class="col-xs-1"></th>
                                  </tr>
                              </thead>
                              <tbody class="drm_non_apurement" id="nonapurement_list">
                                  <?php
                                  foreach ($annexesForm['releve_non_apurement'] as $nonApurementForm) :
                                      include_partial('itemNonApurement', array('form' => $nonApurementForm));
                                  endforeach;
                                  ?>
                                  <?php include_partial('templateNonApurementItem', array('form' => $annexesForm->getFormTemplate())); ?>
                              </tbody>
                              <thead>
                                  <tr>
                                      <td colspan="4" class="ajouter_non_apurement"><a class="btn_ajouter_ligne_template btn btn-sm btn-link pull-right" data-container="#nonapurement_list" data-template="#template_nonapurement" href="#"><span class="glyphicon glyphicon-plus-sign"></span> Ajouter une ligne non apurement</a></td>
                                  </tr>
                              </thead>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-xs-12">
                      <div class="panel panel-default" data-toggle="collapse" data-parent="#accordion" href="#collapse_stats_europeenes" aria-expanded="true" aria-controls="collapse_stats_europeenes">
                        <div class="panel-heading" style="cursor:pointer;" id="drm_annexes_stats_europeenes" >
                            <div class="row">
                              <div class="col-xs-11">
                                <h3 class="panel-title text-center"><strong>Statistiques européennes</strong></h3>
                              </div>
                              <div class="col-xs-1 text-right">
                                <a role="button" >
                                  &nbsp;<span class="glyphicon  glyphicon-chevron-<?php echo ($drm->hasStatsEuropeennes()) ? 'down' : 'right'; ?>" style="padding-top: 4px;" ></span>
                                </a>
                              </div>
                            </div>
                        </div>
                        </div>
                        <div id="collapse_stats_europeenes" class="panel-collapse collapse<?php if ($drm->hasStatsEuropeennes()) { echo " in"; } ?>" role="tabpanel" aria-labelledby="drm_annexes_stats_europeenes">
                        <div class="panel-body">
                          <p><?php echo getPointAideText('drm','annexe_statistique_eur'); ?></p>
                          <table class="table table-bordered table-striped">
                              <thead >
                                <tr>
                                  <th class="col-xs-8" ></th>
                                  <th class="col-xs-3" >Volume</th>
                                  <th class="col-xs-1" ></th>
                              </tr>
                            </thead>
                            <tbody class="drm_annexes_stats_europeenes" id="stats_europeenes_list">
                              <tr>
                                <td class="col-xs-8"><?php echo $annexesForm['statistiques_jus']->renderLabel() ?></td>
                                <td class="col-xs-3"><?php echo $annexesForm['statistiques_jus']->render() ?><?php echo $annexesForm['statistiques_jus']->renderError() ?></td>
                                <td class="col-xs-1" ><span class="unite">hl</span></td>
                              </tr>
                              <tr>
                                <td class="col-xs-8"><?php echo $annexesForm['statistiques_mcr']->renderLabel() ?></td>
                                <td class="col-xs-3"><?php echo $annexesForm['statistiques_mcr']->render() ?><?php echo $annexesForm['statistiques_mcr']->renderError() ?></td>
                                <td class="col-xs-1" ><span class="unite">hl</span></td>
                              </tr>
                              <tr style="border:none;" >
                                <td class="col-xs-8"><?php echo $annexesForm['statistiques_vinaigre']->renderLabel() ?></td>
                                <td class="col-xs-3"><?php echo $annexesForm['statistiques_vinaigre']->render() ?><?php echo $annexesForm['statistiques_vinaigre']->renderError() ?></td>
                                <td class="col-xs-1" ><span class="unite">hl</span></td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                    </div>
                </div>
              </div>
              <?php if($drm->hasObservations()): ?>
                <div class="row">
                  <div class="col-xs-12">
                      <div class="panel panel-default">
                        <div class="panel-heading" style="cursor:pointer;" id="drm_annexes_observations" data-toggle="collapse" data-parent="#accordion" href="#collapse_observations" aria-expanded="true" aria-controls="collapse_observations">
                            <div class="row">
                              <div class="col-xs-11">
                                <h3 class="panel-title text-center"><strong>Observations</strong></h3>
                              </div>
                              <div class="col-xs-1 text-right">
                                <a role="button" >
                                  &nbsp;<span class="glyphicon  glyphicon-chevron-down" style="padding-top: 4px;" ></span>
                                </a>
                              </div>
                            </div>
                        </div>
                      <div id="collapse_observations" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="drm_annexes_observations">
                        <div class="panel-body">
                        <p><?php echo getPointAideText('drm','annexe_observations'); ?></p>
                        <table class="table table-bordered table-striped">
                            <thead >
                              <tr>
                                  <th class="col-xs-4">Produits</th>
                                  <th class="col-xs-8">Observations</th>
                              </tr>
                            </thead>
                            <tbody class="drm_non_apurement" id="nonapurement_list">
                                <?php foreach ($annexesForm['observationsProduits'] as $formObservations): ?>
                                  <?php if(isset($formObservations['observations'])): ?>
                                  <tr>
                                    <td class="col-xs-4" ><?php echo $formObservations['observations']->renderLabel() ?></td>
                                    <td class="col-xs-8" >
                                      <div class="row">
                                          <div class="col-xs-12">
                                          <?php echo $formObservations['observations']->renderError() ?>
                                          <?php echo $formObservations['observations']->render(array("maxlength" => "250", "style" => "width: 95%; box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.4) inset; border-radius: 3px; border: 0px none; padding: 5px;", "rows" => "2")) ?><br/>
                                          </div>
                                          <?php if (isset($formObservations['replacement_date'])): ?>
                                            <div class="col-xs-6">
                                            <?php echo $formObservations['replacement_date']->renderError(); ?>
                                            <?php echo $formObservations['replacement_date']->renderLabel(); ?>
                                            </div>
                                            <div class="col-xs-5">
                                            <?php echo $formObservations['replacement_date']->render(); ?><br/>
                                            </div>
                                          <?php endif; ?>
                                        </div>
                                        </td>
                                  </tr>
                                  <?php endif; ?>
                                <?php endforeach; ?>
                              </tbody>
                            </table>
                                  250 caractères max.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
              <?php endif; ?>

              <?php if(count($annexesForm['tavsProduits'])): ?>
              <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-default">
                      <div class="panel-heading" style="cursor:pointer;" id="drm_annexes_tavs" data-toggle="collapse" data-parent="#accordion" href="#collapse_tavs" aria-expanded="true" aria-controls="collapse_tavs">
                          <div class="row">
                            <div class="col-xs-11">
                              <h3 class="panel-title text-center"><strong>TAV - Taux d'alcool volumique</strong></h3>
                            </div>
                            <div class="col-xs-1 text-right">
                              <a role="button" >
                                &nbsp;<span class="glyphicon  glyphicon-chevron-<?php echo ($drm->hasWritableTAV()) ? 'down' : 'right'; ?>" style="padding-top: 4px;" ></span>
                              </a>
                            </div>
                          </div>
                      </div>
                      <div id="collapse_tavs" class="panel-collapse collapse <?php echo ($drm->hasWritableTAV())? ' in' : '' ?>" role="tabpanel" aria-labelledby="drm_annexes_tavs">
                      <div class="panel-body">
                        <p><?php echo getPointAideText('drm','annexe_tavs'); ?></p>
                        <table class="table table-bordered table-striped">
                            <thead >
                              <tr>
                                  <th class="col-xs-8" >Produits</th>
                                  <th class="col-xs-4" >TAV</th>

                              </tr>
                            </thead>
                            <tbody class="drm_non_apurement" id="tav_list">
                                <?php foreach ($annexesForm['tavsProduits'] as $formTavs): ?>
                                  <?php if(isset($formTavs['tav'])): ?>
                                  <tr>
                                    <td class="col-xs-8" ><?php echo $formTavs['tav']->renderLabel() ?></td>
                                    <td class="col-xs-4" >
                                          <?php echo $formTavs['tav']->renderError() ?>
                                          <?php echo $formTavs['tav']->render(array("style" => "width: 95%; box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.4) inset; border-radius: 3px; border: 0px none; padding: 5px;")) ?><br/>
                                    </td>
                                  </tr>
                                  <?php endif; ?>
                                <?php endforeach; ?>
                              </tbody>
                            </table>
                      </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-xs-4 text-left">
                    <a tabindex="-1" href="<?php echo url_for('drm_crd', $drm); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Etape précédente</a>
                </div>
                <div class="col-xs-4 text-center">
                    <a class="btn btn-default" data-toggle="modal" data-target="#drm_delete_popup" >Supprimer la DRM</a>
                </div>
                <div class="col-xs-4 text-right">
                    <button type="submit" class="btn btn-success">Étape suivante <span class="glyphicon glyphicon-chevron-right"></span></button>
                </div>
            </div>
        </form>
    </div>
</section>
<?php  include_partial('drm/deleteDrmPopup', array('drm' => $drm, 'deleteForm' => $deleteForm)); ?>

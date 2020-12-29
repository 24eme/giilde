<?php
use_helper('Float');
use_helper('Vrac');
use_helper('PointsAides');
 ?>
<?php $contratNonSolde = ((!is_null($form->getObject()->valide->statut)) && ($form->getObject()->valide->statut != VracClient::STATUS_CONTRAT_SOLDE)); ?>

<?php
$etablissementPrincipal = (isset($etablissementPrincipal))? $etablissementPrincipal : null;
 include_partial('vrac/breadcrumbSaisie', array('vrac' => $vrac, 'isTeledeclarationMode' => $isTeledeclarationMode, 'etablissementPrincipal' => $etablissementPrincipal)); ?>

<section id="principal" class="vrac">

<?php if(!$modeStandalone): ?>
<?php include_component('vrac', 'etapes', array('vrac' => $form->getObject(), 'compte' => $compte, 'actif' => 2, 'urlsoussigne' => null, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
<?php endif; ?>


<form action="" method="post" class="form-horizontal" id="contrat_marche" >
    <?php echo $form->renderHiddenFields() ?>
<?php echo $form->renderGlobalErrors() ?>
    <div class="row">
        <div class="col-sm-12">
            <p>
                <span class="<?php echo typeToPictoCssClass($vrac->type_transaction) ?>" style="font-size: 24px;"><?php echo "&nbsp;Contrat de " . showType($vrac); ?></span>
            </p>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><label>Produit</label></h3>
                </div>
                <div class="panel-body">
                    <?php if (in_array($form->getObject()->type_transaction, array(VracClient::TYPE_TRANSACTION_VIN_VRAC, VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE))): ?>
                        <?php if (isset($form['produit'])): ?><?php echo $form['produit']->renderError(); ?><?php endif; ?>
                            <?php if (isset($form['millesime'])): ?><?php echo $form['millesime']->renderError(); ?><?php endif; ?>
                              <div class="form-group">
                                <?php if (isset($form['produit'])): ?>

                                      <div class="col-xs-7 <?php if ($form['produit']->hasError()): ?>has-error<?php endif; ?>">
                                        <div class="row">
                                          <div class="col-xs-1 pull-right">
                                            <?php echo getPointAideHtml('vrac','marche_selection_produit'); ?>
                                          </div>
                                          <div class="col-xs-11" >
                                            <?php echo $form['produit']->render(array('class' => 'form-control select2', 'placeholder' => 'Selectionner un produit', 'tabindex' => '0', 'autofocus' => 'autofocus')); ?>
                                          </div>
                                        </div>
                                      </div>

                                <?php endif; ?>

                                <?php if (isset($form['millesime'])): ?>
                                <div class="form-group">
                                    <div class="col-xs-2 <?php if ($form['millesime']->hasError()): ?>has-error<?php endif; ?>">
                                        <div class="row">
                                          <div class="col-xs-3 pull-right">
                                            <?php echo getPointAideHtml('vrac','marche_millesime'); ?>
                                          </div>
                                          <div class="col-xs-9" >
                                            <?php echo $form['millesime']->render(array('class' => 'form-control select2')); ?>
                                          </div>
                                        </div>
                                    </div>
                                        <?php if (isset($form['millesime_85_15'])): ?>
                                        <div class="col-xs-3 <?php if ($form['millesime_85_15']->hasError()): ?>has-error<?php endif; ?>">
                                          <?php echo $form['millesime_85_15']->renderError(); ?>
                                          <div class="row">
                                              <div class="checkbox">
                                                  <label for="<?php echo $form['millesime_85_15']->renderId(); ?>">
                                                      <?php echo $form['millesime_85_15']->render(); ?>
                                                      <?php echo $form->getWidget('millesime_85_15')->getLabel(); ?>
                                                      &nbsp; <?php echo getPointAideHtml('vrac','marche_millesime_8515'); ?>
                                                  </label>
                                            </div>
                                          </div>
                                      </div>
                                <?php endif; ?>
                                </div>
                              <?php endif; ?>
                              </div>

                        <div class="form-group">
                                <?php if (isset($form['selection'])): ?>
                                <div class="col-xs-8 <?php if ($form['selection']->hasError()): ?>has-error<?php endif; ?>">
                                    <div class="row">
                                        <?php echo $form['selection']->renderError(); ?>
                                        <div class="col-xs-11 col-xs-offset-1" >
                                          <div class="checkbox bloc_condition" data-condition-cible="#bloc_cepage">
                                              <label for="<?php echo $form['selection']->renderId(); ?>">
                                                <?php echo $form['selection']->render(); ?>
                                                  Déclarer un cépage
                                              </label>
                                              &nbsp; <?php echo getPointAideHtml('vrac','marche_cepage'); ?>
                                          </div>
                                        </div>
                                      </div>
                                  </div>
                                <?php endif; ?>
                        </div>
                            <?php if (isset($form['cepage'])): ?><?php echo $form['cepage']->renderError(); ?><?php endif; ?>
                        <div class="form-group" id="bloc_cepage" data-condition-value="1" >
                                <?php if (isset($form['cepage'])): ?>
                                <div class="col-xs-8 <?php if ($form['cepage']->hasError()): ?>has-error<?php endif; ?>">
                                <?php echo $form['cepage']->render(array('class' => 'form-control select2', 'placeholder' => 'Selectionner un cépage', 'tabindex' => '0')); ?>
                                </div>
                            <?php endif; ?>
                                <?php if (isset($form['cepage_85_15'])): ?>
                                <div class="col-xs-4 <?php if ($form['cepage_85_15']->hasError()): ?>has-error<?php endif; ?>">
                                  <?php echo $form['cepage_85_15']->renderError(); ?>
                                  <div class="row">
                                    <div class="col-xs-9 col-xs-offset-3" >
                                      <div class="checkbox">
                                          <label for="<?php echo $form['cepage_85_15']->renderId(); ?>">
                                              <?php echo $form['cepage_85_15']->render(); ?>
                                              <?php echo $form->getWidget('cepage_85_15')->getLabel(); ?>
                                          </label>
                                          &nbsp; <?php echo getPointAideHtml('vrac','marche_cepage_8515'); ?>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                        <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php if (isset($form['cepage'])): ?><?php echo $form['cepage']->renderError(); ?><?php endif; ?>
                            <?php if (isset($form['millesime'])): ?><?php echo $form['millesime']->renderError(); ?><?php endif; ?>
                        <div class="form-group">
                                <?php if (isset($form['cepage'])): ?>
                                <div class="col-xs-8 <?php if ($form['cepage']->hasError()): ?>has-error<?php endif; ?>">
                                <?php echo $form['cepage']->render(array('class' => 'form-control select2', 'placeholder' => 'Selectionner un cépage', 'tabindex' => '0', 'autofocus' => 'autofocus')); ?>
                                </div>
                            <?php endif; ?>
                                <?php if (isset($form['cepage_85_15'])): ?>
                                <div class="col-xs-4 <?php if ($form['cepage_85_15']->hasError()): ?>has-error<?php endif; ?>">
        <?php echo $form['cepage_85_15']->renderError(); ?>
                                    <div class="checkbox">
                                        <label for="<?php echo $form['cepage_85_15']->renderId(); ?>">
                                            <?php echo $form['cepage_85_15']->render(); ?>
        <?php echo $form->getWidget('cepage_85_15')->getLabel(); ?>
                                        </label>
                                    </div>
                                </div>
    <?php endif; ?>
                        </div>
                        <div class="form-group">
                                <?php if (isset($form['selection'])): ?>
                                <div class="col-xs-8 <?php if ($form['selection']->hasError()): ?>has-error<?php endif; ?>">
        <?php echo $form['selection']->renderError(); ?>
                                    <div class="checkbox bloc_condition" data-condition-cible="#bloc_produit">
                                        <label for="<?php echo $form['selection']->renderId(); ?>">
        <?php echo $form['selection']->render(); ?>
                                            Revendiquable en AOP/IGP
                                        </label>
                                    </div>
                                </div>
                        <?php endif; ?>
                        </div>
                            <?php if (isset($form['produit'])): ?><?php echo $form['produit']->renderError(); ?><?php endif; ?>
                        <div class="form-group" id="bloc_produit" data-condition-value="1" >
                                <?php if (isset($form['produit'])): ?>
                                <div class="col-xs-8 <?php if ($form['produit']->hasError()): ?>has-error<?php endif; ?>">
                                <?php echo $form['produit']->render(array('class' => 'form-control select2', 'placeholder' => 'Selectionner un produit', 'tabindex' => '0')); ?>
                                </div>
                        <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($form['label'])): ?><?php echo $form['label']->renderError(); ?><?php endif; ?>
                    <?php if (isset($form['label'])): ?>
                    <?php echo $form['label']->renderError(); ?>
                        <div class="form-group">
                                <div class="col-xs-8 <?php if ($form['label']->hasError()): ?>has-error<?php endif; ?>">
                                      <div class="row">
                                        <div class="col-xs-11 col-xs-offset-1" >
                                          <?php echo $form['label']->render(); ?>
                                          &nbsp; <?php echo getPointAideHtml('vrac','marche_agriculture_bio'); ?>
                                        </div>
                                      </div>
                                </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><label>Informations complémentaires</label></h3>
                        </div>
                        <div class="panel-body">
                            <div class="col-sm-12">
<?php if (isset($form['lot'])): ?>
                                <div class="col-sm-6">
<?php echo $form['lot']->renderError(); ?>
                                </div>
<?php endif; ?>
<?php if (isset($form['degre'])): ?>
                                <div class="col-sm-6">
<?php echo $form['degre']->renderError(); ?>
                                </div>
<?php endif; ?>
                            </div>
                            <div class="col-sm-12">
                              <div class="row">
                                    <?php if (isset($form['lot'])): ?>
                                    <div class="form-group col-xs-6 <?php if ($form['lot']->hasError()): ?>has-error<?php endif; ?>">
                                            <?php echo $form['lot']->renderLabel("N° de lot :", array('class' => 'col-sm-4 control-label')); ?>
                                            &nbsp; <?php echo getPointAideHtml('vrac','marche_numero_lot'); ?>
                                        <div class="col-sm-5">
    <?php echo $form['lot']->render(array("class" => "form-control text-right", 'autocomplete' => 'off')); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                    <?php if (isset($form['degre'])): ?>
                                    <div class="form-group col-xs-6 <?php if ($form['degre']->hasError()): ?>has-error<?php endif; ?>">
                                      <?php echo $form['degre']->renderLabel("Degré :", array('class' => 'col-sm-2 control-label')); ?>
                                      &nbsp; <?php echo getPointAideHtml('vrac','marche_degre'); ?>
                                        <div class="col-sm-5">
                                            <div class="input-group">
                                              <?php echo $form['degre']->render(); ?>
                                                <span class="input-group-addon">&nbsp;°&nbsp;&nbsp;</span>
                                            </div>
                                        </div>
                                    </div>
                            <?php endif; ?>
                            </div>
                            </div>
                            <?php if (isset($form['surface'])): ?>
                                    <?php echo $form['surface']->renderError(); ?>
                                <div class="form-group col-xs-6 <?php if ($form['surface']->hasError()): ?>has-error<?php endif; ?>">
    <?php echo $form['surface']->renderLabel("Surface :", array('class' => 'col-sm-4 control-label')); ?>
                                    <div class="col-sm-5">
                                        <div class="input-group">
    <?php echo $form['surface']->render(); ?>
                                            <span class="input-group-addon">&nbsp;<?php echo $configuration->getUnites()[$form->getObject()->type_transaction]['surface']['libelle'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($form['categorie_vin'])): ?><?php echo $form['categorie_vin']->renderError(); ?><?php endif; ?>
                                <?php if (isset($form['domaine'])): ?><?php echo $form['domaine']->renderError(); ?><?php endif; ?>
                                <div class="form-group col-sm-6">
                                <?php if (isset($form['categorie_vin'])): ?>
                                    <div class="col-sm-1 pull-right" style="top: -20px;left: -70px;"><?php echo getPointAideHtml('vrac','marche_mention'); ?></div>
                                    <?php echo $form['categorie_vin']->renderLabel("Type :&nbsp;", array('class' => 'col-sm-4 control-label')); ?>
                                    <div class="bloc_condition col-sm-6 <?php if ($form['categorie_vin']->hasError()): ?>has-error<?php endif; ?>" data-condition-cible="#bloc_domaine">
                                    <?php echo $form['categorie_vin']->render(); ?>
                                    </div>
                                <?php endif; ?>
                              </div>
                                <?php if (isset($form['domaine'])): ?>
                                  <div class="col-xs-6">
                                    <div id="bloc_domaine" data-condition-value="MENTION" class="col-sm-5 col-sm-offset-3  <?php if ($form['domaine']->hasError()): ?>has-error<?php endif; ?>">
                                      <?php echo $form['domaine']->render(array('class' => 'form-control select2permissifNoAjax', 'placeholder' => 'Déclarer une mention (Chateau, Domaine…)', "data-choices" => json_encode($form->getDomainesForAutocomplete()))); ?>
                                    </div>
                                  </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
              <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><label>Quantité et prix</label></h3>
                        </div>
                        <div class="panel-body">
<?php if (isset($form['bouteilles_contenance_libelle'])): ?>
                                <script type="text/javascript">
                                    var contenances = new Array();
    <?php foreach (VracConfiguration::getInstance()->getContenances() as $l => $hl): ?>
                                        contenances["<?php echo $l ?>"] = <?php echo $hl ?>;
    <?php endforeach; ?>
                                </script>
                                <div class="form-group col-xs-4 <?php if ($form['bouteilles_contenance_libelle']->hasError()): ?>has-error<?php endif; ?>">
                                    <div class="col-xs-12"><?php echo $form['bouteilles_contenance_libelle']->renderError(); ?></div>
                                        <?php echo $form['bouteilles_contenance_libelle']->renderLabel("Contenance :", array('class' => 'col-sm-5 control-label')); ?>
                                    <div class="col-sm-7">
                                      <?php echo $form['bouteilles_contenance_libelle']->render(array('class' => 'form-control')); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

<?php if (isset($form['jus_quantite'])): ?>
                                <div class="form-group <?php if (isset($form['bouteilles_contenance_libelle'])): ?>col-xs-4<?php else: ?>col-xs-6<?php endif; ?> <?php if ($form['jus_quantite']->hasError()): ?>has-error<?php endif; ?>">
                                    <div class="col-xs-12"><?php echo $form['jus_quantite']->renderError(); ?></div>
                                    <div class="col-sm-1 pull-right" style="top: -20px;" ><?php echo getPointAideHtml('vrac','marche_volume'); ?></div>
                                    <?php echo $form['jus_quantite']->renderLabel("Volume :", array('class' => 'col-sm-4 control-label')); ?>
                                    <div class="col-sm-6">
                                        <div class="input-group">
                                            <?php echo $form['jus_quantite']->render(); ?>
                                            <span class="input-group-addon">&nbsp;<?php echo $configuration->getUnites()[$form->getObject()->type_transaction]['jus_quantite']['libelle'] ?></span>
                                        </div>
                                        <?php if (isset($form['bouteilles_contenance_libelle'])): ?>
                                            <p class="help-block pull-right" id="correspondance_bouteille"></p>
    <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
<?php if (isset($form['raisin_quantite'])): ?>
                                <div class="form-group <?php if (isset($form['bouteilles_contenance_libelle'])): ?>col-xs-4<?php else: ?>col-xs-6<?php endif; ?> <?php if ($form['raisin_quantite']->hasError()): ?>has-error<?php endif; ?>">
                                    <div class="col-xs-12">
                                    <?php echo $form['raisin_quantite']->renderError(); ?>
                                    </div>
    <?php echo $form['raisin_quantite']->renderLabel("Quantité :", array('class' => 'col-sm-4 control-label')); ?>
                                    <div class="col-sm-7">
                                        <div class="input-group">
    <?php echo $form['raisin_quantite']->render(); ?>
                                            <span class="input-group-addon"><?php echo $configuration->getUnites()[$form->getObject()->type_transaction]['raisin_quantite']['libelle'] ?>&nbsp;&nbsp;&nbsp;</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
<?php if (isset($form['prix_initial_unitaire'])): ?>
                                <div class="form-group <?php if (isset($form['bouteilles_contenance_libelle'])): ?>col-xs-4<?php else: ?>col-xs-6<?php endif; ?> <?php if ($form['prix_initial_unitaire']->hasError()): ?>has-error<?php endif; ?>">
                                    <div class="col-xs-12">
                                    <?php echo $form['prix_initial_unitaire']->renderError(array('class' => ' col-xs-10 col-xs-offset-1')); ?>
                                    </div>
                                    <div class="col-sm-1 pull-right" style="top:-20px;" ><?php echo getPointAideHtml('vrac','marche_prix'); ?></div>
    <?php echo $form['prix_initial_unitaire']->renderLabel("Prix :", array('class' => 'col-sm-3 control-label')); ?>
                                    <div class="col-sm-7">
                                        <div class="input-group">
    <?php echo $form['prix_initial_unitaire']->render(); ?>
                                            <span class="input-group-addon"><?php echo $configuration->getUnites()[$form->getObject()->type_transaction]['prix_initial_unitaire']['libelle'] ?></span>
                                        </div>
                                    </div>
                                </div>
<?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-4 col-md-push-8 text-right">
            <button type="submit" class="btn btn-success">
                <?php if($modeStandalone): ?>
                Valider
                <?php else: ?>
                Étape suivante <span class="glyphicon glyphicon-chevron-right"></span>
                <?php endif; ?>
            </button>
        </div>
        <div class="col-xs-4 text-center">
            <?php if (!$modeStandalone && $isTeledeclarationMode && $vrac->isBrouillon()) : ?>
                <a class="btn btn-default" href="<?php echo url_for('vrac_supprimer_brouillon', $vrac); ?>">Supprimer le brouillon</a>
            <?php endif; ?>
            <?php if (!$modeStandalone && !$isTeledeclarationMode) : ?>
                <button type="submit" tabindex="-1" name="redirect" value="<?php echo url_for('vrac'); ?>" class="btn btn-default" ><span class="glyphicon glyphicon-floppy-disk"></span> Enregistrer en brouillon</button>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 col-md-pull-8 text-left">
            <?php if($modeStandalone && $urlRetour): ?>
                <a href="<?php echo $urlRetour; ?>" tabindex="-1" class="btn btn-default">Annuler</a>
            <?php else: ?>
                <button type="submit" formnovalidate="formnovalidate" tabindex="-1" name="redirect" value="<?php echo url_for('vrac_soussigne',$vrac); ?>" class="btn btn-default">
                    <span class="glyphicon glyphicon-chevron-left"></span> Etape précédente
                </button>
            <?php endif; ?>
        </div>
    </div>

</form>
</section>

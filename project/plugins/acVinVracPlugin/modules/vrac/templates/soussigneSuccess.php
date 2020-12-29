<?php use_helper('Vrac');
use_helper('PointsAides');

$urlForm = null;
if (($form->getObject()->isNew() && !isset($isTeledeclarationMode)) || ($form->getObject()->isNew() && !$isTeledeclarationMode)) :
    $urlForm = url_for('vrac_nouveau');
elseif ($form->getObject()->isNew() && isset($isTeledeclarationMode) && $isTeledeclarationMode) :
    if (isset($choixEtablissement) && $choixEtablissement):
        $urlForm = url_for('vrac_nouveau', array('choix-etablissement' => $choixEtablissement));
    else:
        $urlForm = url_for('vrac_nouveau', array('etablissement' => $etablissementPrincipal->identifiant));
    endif;
else :
    $urlForm = url_for('vrac_soussigne', $vrac);
endif;
?>

<?php
$etablissementPrincipal = (isset($etablissementPrincipal))? $etablissementPrincipal : null;
include_partial('vrac/breadcrumbSaisie', array('vrac' => $vrac, 'isTeledeclarationMode' => $isTeledeclarationMode, 'etablissementPrincipal' => $etablissementPrincipal));
?>

<section id="principal" class="vrac">

<?php include_component('vrac', 'etapes', array('vrac' => $form->getObject(), 'compte' => $compte, 'actif' => 1, 'urlsoussigne' => $urlForm,'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<form
	id="contrat_soussignes"
	data-numcontrat="<?php echo ($nouveau)? null : $form->getObject()->numero_contrat ;?>"
	data-isteledeclare="<?php echo ($isTeledeclarationMode)? 1 : 0 ;?>"
	data-etablissementprincipal="<?php if (isset($etablissementPrincipal) && $etablissementPrincipal) echo $etablissementPrincipal->_id ?>"
	data-iscourtierresponsable="<?php echo (isset($isCourtierResponsable) && $isCourtierResponsable)? 1 : 0 ?>"
	action="<?php echo $urlForm; ?>"
	method="post"
	class="form-horizontal"
>
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
    <?php if(isset($form['responsable'])): ?>
    <?php echo $form['responsable']->renderError(); ?>
    <?php endif; ?>
    <div class="row">
        <div class="col-sm-12">
        	<?php if(isset($form['attente_original'])): ?>
            <div class="form-group <?php if($form['attente_original']->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form['attente_original']->renderError(); ?>
                <?php echo $form['attente_original']->renderLabel("En attente de l'original :", array('class' => 'col-sm-3 control-label')); ?>
                <div class="col-sm-9">
                    <?php echo $form['attente_original']->render(); ?>
                </div>
            </div>
            <?php endif; ?>
        	<?php if(isset($form['type_contrat'])): ?>
            <div class="form-group <?php if($form['type_contrat']->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form['type_contrat']->renderError(); ?>
                <?php echo $form['type_contrat']->renderLabel("Contrat pluriannuel :", array('class' => 'col-sm-3 control-label')); ?>
                <div class="col-sm-9">
                    <?php echo $form['type_contrat']->render(); ?>
                </div>
            </div>
            <?php endif; ?>
        	<?php if(isset($form['type_transaction'])): ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><label>Type de transaction</label><?php echo getPointAideHtml('vrac','soussignes_type_contrat'); ?></h3>
                </div>
                <div class="panel-body">
                    <?php echo $form['type_transaction']->renderError(); ?>
                    <div class="form-group <?php if($form['type_transaction']->hasError()): ?>has-error<?php endif; ?>">
                        <div class="col-sm-12 text-center">
                            <?php echo $form['type_transaction']->render(array('autofocus' => 'autofocus')); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-xs-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><label>Vendeur</label>
                            <?php if(isset($form['responsable']) && !$vrac->teledeclare): ?>
                            <label class="responsable pointer text-right pull-right <?php if($vrac->isVendeurResponsable()): ?> text-success<?php else: ?> text-muted<?php endif; ?>">
                              <input autocomplete="off" type="radio" name="vrac[responsable]" id="vrac_responsable_vendeur" value="vendeur" autocomplete="off"<?php if($vrac->isVendeurResponsable()): ?> checked<?php endif; ?> /><span class="glyphicon glyphicon-user" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Responsable"></span>
                        </label>
							<?php endif; ?>
							</h3>
                        </div>
                        <div class="panel-body">
                            <?php if (isset($form['vendeur_identifiant'])): ?>
                            <div class="row">
                                  <div id="vendeur_selection" class="col-sm-12 <?php if($form['vendeur_identifiant']->getValue()): ?>hidden<?php endif; ?>">
                                    <div class="row">
                                      <div class="col-sm-1 pull-right"><?php echo getPointAideHtml('vrac','soussignes_selection_vendeur'); ?>&nbsp;&nbsp;</div>
                                      <div class="col-sm-11">
                                        <?php echo $form['vendeur_identifiant']->renderError(); ?>
                                        <div class="form-group <?php if($form['vendeur_identifiant']->hasError()): ?>has-error<?php endif; ?>">
                                              <?php if($isTeledeclarationMode): ?>
                                                  <div class="col-sm-12" id="vendeur_choice">
                                                    <?php echo $form['vendeur_identifiant']->render(array('class' => 'form-control select2-soussigne-teledeclaration select-ajax', 'placeholder' => 'Sélectionner un vendeur', 'data-url' => url_for('vrac_soussigne_getinfos'),'data-annuaire-link' => url_for('annuaire_selectionner', array('identifiant' => $etablissementPrincipal->identifiant,'type' => 'recoltants')), 'data-bloc' => '#vendeur_informations', 'data-hide' => '#vendeur_selection')); ?>
                                                    <?php $style_vendeur_compte_inactif = ($compteVendeurActif) ? 'style="display: none;"' : ""; ?>
                                                  </div>
                                              <?php else : ?>
                                                  <div class="col-sm-12" id="vendeur_choice">
                                                    <?php echo $form['vendeur_identifiant']->render(array('class' => 'form-control select2autocomplete select-ajax', 'placeholder' => 'Sélectionner un vendeur', 'data-url' => url_for('vrac_soussigne_getinfos'), 'data-bloc' => '#vendeur_informations', 'data-hide' => '#vendeur_selection')); ?>
                                                  </div>
                                              <?php endif; ?>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 text-center <?php if(!$form['vendeur_identifiant']->getValue()): ?>hidden<?php endif; ?>" id="vendeur_informations">
                                    <div class="pull-right">
                                    <button type="button" class="btn btn-xs btn-default select-close" data-select="#<?php echo $form['vendeur_identifiant']->renderId() ?>"><span class="glyphicon glyphicon-remove"></span></button>
                                    <span>&nbsp;<?php echo getPointAideHtml('vrac','soussigne_recoltant_supprimer'); ?></span>
                                    </div>
                                    <div class="container-ajax">
                                        <?php if($form['vendeur_identifiant']->getValue()): ?>
                                        <?php include_partial('vrac/soussigne', array('id' => $form['vendeur_identifiant']->getValue(), 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-sm-12">
				                   <?php if(isset($form['logement'])): ?>
                                    <div class="form-group <?php if($form['logement_exist']->hasError()): ?>has-error<?php endif; ?>">
                		                    <?php echo $form['logement_exist']->renderError(); ?>
                		                    <div class="checkbox col-sm-12 bloc_condition" data-condition-cible="#bloc_logement">
                		                        <label for="<?php echo $form['logement_exist']->renderId(); ?>">
                		                            <?php echo $form['logement_exist']->render(); ?>
                		                            Vin logé à une autre adresse
                		                        </label>
                                                <span>&nbsp;&nbsp;<?php echo getPointAideHtml('vrac','soussignes_vin_loge'); ?></span>
                		                    </div>
                		            </div>
                		            <div id="bloc_logement" data-condition-value="1" class="form-group bloc_conditionner <?php if($form['logement']->hasError()): ?>has-error<?php endif; ?>">
                		                <?php echo $form['logement']->renderError(); ?>
                		                <div class="col-sm-12">
                		                    <?php echo $form['logement']->render(array("placeholder" => "Ville du logement")); ?>
                		                </div>
                		            </div>
                		            <?php endif; ?>
						            <?php if(isset($form['vendeur_intermediaire']) && isset($form['representant_identifiant'])): ?>
                		            <div class="form-group">
                		            	<?php echo $form['vendeur_intermediaire']->renderError(); ?>
            		                    <div class="checkbox col-sm-12 bloc_condition" data-condition-cible="#bloc_intermediaire">
            		                	       <label for="<?php echo $form['vendeur_intermediaire']->renderId(); ?>">
            		                    	        <?php echo $form['vendeur_intermediaire']->render(); ?>
            		                    	           Vente via intermédiaire
            		                        </label>
                                            <span>&nbsp;&nbsp;<?php echo getPointAideHtml('vrac','soussignes_vente_intermediaire'); ?></span>
            		                    </div>
                		            </div>
                		            <div id="bloc_intermediaire" data-condition-value="1" class="form-group bloc_conditionner">
		                                <div id="representant_selection" class="col-sm-12 <?php if($form['representant_identifiant']->getValue()): ?>hidden<?php endif; ?>">
		                                    <?php echo $form['representant_identifiant']->renderError(); ?>
                                        <?php if($isTeledeclarationMode): ?>
                                          <div class="form-group <?php if($form['representant_identifiant']->hasError()): ?>has-error<?php endif; ?>">
                                            <div class="col-sm-11" id="representant_choice">
                                              <?php echo $form['representant_identifiant']->render(array('class' => 'form-control select2', 'placeholder' => 'Sélectionner un représentant', 'data-url' => url_for('vrac_soussigne_getinfos'), 'data-bloc' => '#representant_informations', 'data-hide' => '#representant_selection')); ?>
                                            </div>
                                          </div>
                                        <?php else: ?>
                                          <div class="form-group <?php if($form['representant_identifiant']->hasError()): ?>has-error<?php endif; ?>">
                                            <div class="col-sm-12" id="representant_choice">
                                              <?php echo $form['representant_identifiant']->render(array('class' => 'form-control select2autocomplete select-ajax', 'placeholder' => 'Sélectionner un représentant', 'data-url' => url_for('vrac_soussigne_getinfos'), 'data-bloc' => '#representant_informations', 'data-hide' => '#representant_selection')); ?>
                                            </div>
                                          </div>
                                        <?php endif; ?>
		                                </div>
                                        <div class="col-sm-12 text-center <?php if(!$form['representant_identifiant']->getValue()): ?>hidden<?php endif; ?>" id="representant_informations">
                                            <div class="pull-right">
                                                <button type="button" class="btn btn-xs btn-default select-close" data-select="#<?php echo $form['representant_identifiant']->renderId() ?>"><span class="glyphicon glyphicon-remove"></span></button>
                                                &nbsp;<?php echo getPointAideHtml('vrac','soussigne_recoltant_supprimer'); ?>
                                            </div>
    		                                    <div class="container-ajax">
    		                                        <?php if($form['representant_identifiant']->getValue()): ?>
    		                                        <?php include_partial('vrac/soussigne', array('id' => $form['representant_identifiant']->getValue(), 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
    		                                        <?php endif; ?>
    		                                    </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
        							          <?php if(isset($form['vendeur_tva'])): ?>
                                    <div class="form-group <?php if($form['vendeur_tva']->hasError()): ?>has-error<?php endif; ?>" >
                  		                <div class="col-sm-12">
                  		                    <?php echo $form['vendeur_tva']->renderError(); ?>
                  		                    <div class="checkbox">
                  		                        <label for="<?php echo $form['vendeur_tva']->renderId(); ?>">
                  		                            <?php echo $form['vendeur_tva']->render(); ?>
                  		                            Le vendeur est assujetti à la TVA
                  		                        </label>
                                                <span>&nbsp;&nbsp;<?php echo getPointAideHtml('vrac','soussignes_non_tva'); ?></span>
                  		                    </div>
                  		                </div>
                		                </div>
                		            <?php endif; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="row">
                                        <div class="col-sm-12 text-center" id="representant_informations">
                                            <div class="container-ajax">
                                                <?php include_partial('vrac/soussigne', array('id' => $vrac->vendeur_identifiant, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                           <?php if(isset($form['logement'])): ?>
                                            <div class="form-group <?php if($form['logement_exist']->hasError()): ?>has-error<?php endif; ?>">
                                                    <?php echo $form['logement_exist']->renderError(); ?>
                                                    <div class="checkbox col-sm-12 bloc_condition" data-condition-cible="#bloc_logement">
                                                        <label for="<?php echo $form['logement_exist']->renderId(); ?>">
                                                            <?php echo $form['logement_exist']->render(); ?>
                                                            Vin logé à une autre adresse
                                                        </label>
                                                        <span>&nbsp;&nbsp;<?php echo getPointAideHtml('vrac','soussignes_vin_loge'); ?></span>
                                                    </div>
                                            </div>
                                            <div id="bloc_logement" data-condition-value="1" class="form-group bloc_conditionner <?php if($form['logement']->hasError()): ?>has-error<?php endif; ?>">
                                                <?php echo $form['logement']->renderError(); ?>
                                                <div class="col-sm-12">
                                                    <?php echo $form['logement']->render(array("placeholder" => "Ville du logement")); ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <?php if(isset($form['vendeur_intermediaire']) && isset($form['representant_identifiant'])): ?>
                                            <div class="form-group">
                                                <?php echo $form['vendeur_intermediaire']->renderError(); ?>
                                                <div class="checkbox col-sm-12 bloc_condition" data-condition-cible="#bloc_intermediaire">
                                                       <label for="<?php echo $form['vendeur_intermediaire']->renderId(); ?>">
                                                            <?php echo $form['vendeur_intermediaire']->render(); ?>
                                                               Vente via intermédiaire
                                                    </label>
                                                    <span>&nbsp;&nbsp;<?php echo getPointAideHtml('vrac','soussignes_vente_intermediaire'); ?></span>
                                                </div>
                                            </div>
                                            <div id="bloc_intermediaire" data-condition-value="1" class="form-group bloc_conditionner">
                                                <div id="representant_selection" class="col-sm-12 <?php if($form['representant_identifiant']->getValue()): ?>hidden<?php endif; ?>">
                                                    <?php echo $form['representant_identifiant']->renderError(); ?>
                                                <?php if($isTeledeclarationMode): ?>
                                                  <div class="form-group <?php if($form['representant_identifiant']->hasError()): ?>has-error<?php endif; ?>">
                                                    <div class="col-sm-11" id="representant_choice">
                                                      <?php echo $form['representant_identifiant']->render(array('class' => 'form-control select2', 'placeholder' => 'Sélectionner un représentant', 'data-url' => url_for('vrac_soussigne_getinfos'), 'data-bloc' => '#representant_informations', 'data-hide' => '#representant_selection')); ?>
                                                    </div>
                                                  </div>
                                                <?php else: ?>
                                                  <div class="form-group <?php if($form['representant_identifiant']->hasError()): ?>has-error<?php endif; ?>">
                                                    <div class="col-sm-12" id="representant_choice">
                                                      <?php echo $form['representant_identifiant']->render(array('class' => 'form-control select2autocomplete select-ajax', 'placeholder' => 'Sélectionner un représentant', 'data-url' => url_for('vrac_soussigne_getinfos'), 'data-bloc' => '#representant_informations', 'data-hide' => '#representant_selection')); ?>
                                                    </div>
                                                  </div>
                                                <?php endif; ?>
                                                </div>
                                                <div class="col-sm-12 text-center <?php if(!$form['representant_identifiant']->getValue()): ?>hidden<?php endif; ?>" id="representant_informations">
                                                    <div class="pull-right">
                                                        <button type="button" class="btn btn-xs btn-default select-close" data-select="#<?php echo $form['representant_identifiant']->renderId() ?>"><span class="glyphicon glyphicon-remove"></span></button>
                                                        &nbsp;<?php echo getPointAideHtml('vrac','soussigne_recoltant_supprimer'); ?>
                                                    </div>
                                                        <div class="container-ajax">
                                                            <?php if($form['representant_identifiant']->getValue()): ?>
                                                            <?php include_partial('vrac/soussigne', array('id' => $form['representant_identifiant']->getValue(), 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
                                                            <?php endif; ?>
                                                        </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                                      <?php if(isset($form['vendeur_tva'])): ?>
                                            <div class="form-group <?php if($form['vendeur_tva']->hasError()): ?>has-error<?php endif; ?>" >
                                                <div class="col-sm-12">
                                                    <?php echo $form['vendeur_tva']->renderError(); ?>
                                                    <div class="checkbox">
                                                        <label for="<?php echo $form['vendeur_tva']->renderId(); ?>">
                                                            <?php echo $form['vendeur_tva']->render(); ?>
                                                            Le vendeur est assujetti à la TVA
                                                        </label>
                                                        <span>&nbsp;&nbsp;<?php echo getPointAideHtml('vrac','soussignes_non_tva'); ?></span>
                                                    </div>
                                                </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-xs-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title"><label>Acheteur</label>
                            <?php if(isset($form['responsable']) && !$vrac->teledeclare): ?>
                            <label class="responsable pointer text-right pull-right<?php if($vrac->isAcheteurResponsable()): ?>  text-success<?php else: ?> text-muted<?php endif; ?>">
							    <input autocomplete="off" type="radio" name="vrac[responsable]" id="vrac_responsable_acheteur" value="acheteur" autocomplete="off"<?php if($vrac->isAcheteurResponsable()): ?> checked<?php endif; ?> /><span class="glyphicon glyphicon-user" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Responsable"></span>
							</label>
							<?php endif; ?>
							</h3>
                        </div>
                        <div class="panel-body">
                        <?php if (isset($form['acheteur_negociant'])): ?>
                            <div class="row">
                            	<div id="bloc_acheteur_type" class="col-sm-12 <?php if($form['acheteur_producteur']->getValue() || $form['acheteur_negociant']->getValue()): ?>hidden<?php endif; ?>">
                            <?php echo $form['acheteur_type']->renderError(); ?>
				                    <div class="form-group <?php if($form['acheteur_type']->hasError()): ?>has-error<?php endif; ?>">
				                        <div class="col-sm-11 bloc_condition" data-condition-cible="#bloc_producteur|#bloc_negociant">
				                            <?php echo $form['acheteur_type']->render(); ?>
                                            <span>&nbsp;&nbsp;<?php echo getPointAideHtml('vrac','soussignes_nature_acheteur'); ?></span>
				                        </div>
				                    </div>
                            	</div>
                            	<div id="bloc_producteur" data-condition-value="<?php echo EtablissementFamilles::FAMILLE_PRODUCTEUR?>">
                                  <div id="acheteur_producteur_selection" class="col-sm-12 <?php if($form['acheteur_producteur']->getValue()): ?>hidden<?php endif; ?>">
                                    <div class="row">
                                      <div class="col-sm-1 pull-right"><?php echo getPointAideHtml('vrac','soussignes_selection_acheteur'); ?></div>
                                      <div class="col-sm-11">
                                        <?php echo $form['acheteur_producteur']->renderError(); ?>
    	                                    <div class="form-group <?php if($form['acheteur_producteur']->hasError()): ?>has-error<?php endif; ?>">
                                            <?php if($isTeledeclarationMode): ?>
                                              <div class="col-sm-12" id="acheteur_producteur_choice">
                                                <?php echo $form['acheteur_producteur']->render(array('class' => 'form-control select2-soussigne-teledeclaration select-ajax', 'placeholder' => 'Sélectionner un acheteur', 'data-url' => url_for('vrac_soussigne_getinfos'),'data-annuaire-link' => url_for('annuaire_selectionner', array('identifiant' => $etablissementPrincipal->identifiant,'type' => 'negociants')), 'data-bloc' => '#acheteur_producteur_informations', 'data-hide' => '#acheteur_producteur_selection, #bloc_acheteur_type')); ?>
                                              </div>
                                            <?php else: ?>
    	                                        <div class="col-sm-12" id="acheteur_producteur_choice">
                                                <?php echo $form['acheteur_producteur']->render(array('class' => 'form-control select2autocomplete select-ajax', 'placeholder' => 'Sélectionner un acheteur', 'data-url' => url_for('vrac_soussigne_getinfos'), 'data-bloc' => '#acheteur_producteur_informations', 'data-hide' => '#acheteur_producteur_selection, #bloc_acheteur_type')); ?>
    	                                        </div>
                                            <?php endif; ?>
    	                                    </div>
    	                                </div>
                                    </div>
                                  </div>
	                                <div class="col-sm-12 text-center <?php if(!$form['acheteur_producteur']->getValue()): ?>hidden<?php endif; ?>" id="acheteur_producteur_informations">
                                        <div class="pull-right">
	                                        <button type="button" class="btn btn-xs btn-default select-close" data-select="#<?php echo $form['acheteur_producteur']->renderId() ?>"><span class="glyphicon glyphicon-remove"></span></button>
                                            <span>&nbsp;<?php echo getPointAideHtml('vrac','soussigne_acheteur_supprimer'); ?></span>
                                        </div>
	                                    <div class="container-ajax">
	                                        <?php if($form['acheteur_producteur']->getValue()): ?>
	                                        <?php include_partial('vrac/soussigne', array('id' => $form['acheteur_producteur']->getValue(), 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
	                                        <?php endif; ?>
	                                    </div>
	                                </div>
	                            </div>
                            	<div id="bloc_negociant" data-condition-value="<?php echo EtablissementFamilles::FAMILLE_NEGOCIANT ?>">
                                  <div id="acheteur_negociant_selection" class="col-sm-12 <?php if($form['acheteur_negociant']->getValue()): ?>hidden<?php endif; ?>">
                                    <div class="row">
                                      <div class="col-sm-1 pull-right"><?php echo getPointAideHtml('vrac','soussignes_selection_acheteur'); ?></div>
                                      <div class="col-sm-11">
                                        <?php echo $form['acheteur_negociant']->renderError(); ?>
  	                                    <div class="form-group <?php if($form['acheteur_negociant']->hasError()): ?>has-error<?php endif; ?>">
                                          <?php if($isTeledeclarationMode): ?>
  	                                        <div class="col-sm-12" id="acheteur_negociant_choice">
                                              <?php echo $form['acheteur_negociant']->render(array('class' => 'form-control select2-soussigne-teledeclaration select-ajax', 'placeholder' => 'Sélectionner un acheteur', 'data-url' => url_for('vrac_soussigne_getinfos'),'data-annuaire-link' => url_for('annuaire_selectionner', array('identifiant' => $etablissementPrincipal->identifiant,'type' => 'negociants')), 'data-bloc' => '#acheteur_negociant_informations', 'data-hide' => '#acheteur_negociant_selection, #bloc_acheteur_type')); ?>
                                            </div>
                                          <?php else: ?>
  	                                        <div class="col-sm-12" id="acheteur_negociant_choice">
                                              <?php echo $form['acheteur_negociant']->render(array('class' => 'form-control select2autocomplete select-ajax', 'placeholder' => 'Sélectionner un acheteur', 'data-url' => url_for('vrac_soussigne_getinfos'), 'data-bloc' => '#acheteur_negociant_informations', 'data-hide' => '#acheteur_negociant_selection, #bloc_acheteur_type')); ?>
  	                                        </div>
                                          <?php endif; ?>
  	                                    </div>
                                      </div>
                                    </div>
	                                </div>
	                                <div class="col-sm-12 text-center <?php if(!$form['acheteur_negociant']->getValue()): ?>hidden<?php endif; ?>" id="acheteur_negociant_informations">
                                        <div class="pull-right">
	                                        <button type="button" class="btn btn-xs btn-default select-close" data-select="#<?php echo $form['acheteur_negociant']->renderId() ?>"><span class="glyphicon glyphicon-remove"></span></button>
                                            &nbsp; <?php echo getPointAideHtml('vrac','soussigne_acheteur_supprimer'); ?>
                                        </div>
	                                    <div class="container-ajax">
	                                        <?php if($form['acheteur_negociant']->getValue()): ?>
	                                        <?php include_partial('vrac/soussigne', array('id' => $form['acheteur_negociant']->getValue(), 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
	                                        <?php endif; ?>
	                                    </div>
	                                </div>
	                            </div>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-sm-12 text-center" id="acheteur_negociant_informations">
                                    <div>
                                        <?php include_partial('vrac/soussigne', array('id' => $vrac->acheteur_identifiant, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        	<?php if(isset($form['mandataire_identifiant'])): ?>
            <?php echo $form['mandataire_exist']->renderError(); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                    	<span class="bloc_condition" data-condition-cible="#bloc_mandataire"  for="<?php echo $form['mandataire_exist']->renderId(); ?>"><?php echo $form['mandataire_exist']->render(); ?>&nbsp;&nbsp;<label for="vrac_mandataire_exist">Mandataire / Courtier</label></span>
                    <?php if(isset($form['responsable']) && !$vrac->teledeclare): ?>
                    <label class="responsable pointer pull-right<?php if($vrac->isMandataireResponsable()): ?>  text-success<?php else: ?> text-muted<?php endif; ?>">
						<input autocomplete="off" type="radio" name="vrac[responsable]" id="vrac_responsable_mandataire" value="mandataire" autocomplete="off"<?php if($vrac->isMandataireResponsable()): ?> checked<?php endif; ?> /><span class="glyphicon glyphicon-user" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Responsable"></span>
					</label>
					<?php endif; ?>
					</h3>
                </div>
                <div id="bloc_mandataire" data-condition-value="1" class="panel-body bloc_conditionner">
                    <div class="row">
                        <div class="col-sm-12 <?php if($form['mandataire_identifiant']->getValue()): ?>hidden<?php endif; ?>" id="mandataire_selection">
                            <?php echo $form['mandataire_identifiant']->renderError(); ?>
                            <div class="form-group <?php if($form['mandataire_identifiant']->hasError()): ?>has-error<?php endif; ?>">
                              <?php if($isTeledeclarationMode): ?>
                                <div class="col-sm-12" id="mandataire_choice">
                                  <?php echo $form['mandataire_identifiant']->render(array('class' => 'form-control select2', 'placeholder' => 'Sélectionner un mandataire', 'data-url' => url_for('vrac_soussigne_getinfos'),'data-annuaire-link' => url_for('annuaire_selectionner', array('identifiant' => $etablissementPrincipal->identifiant,'type' => 'courtier')), 'data-bloc' => '#mandataire_informations', 'data-hide' => '#mandataire_selection')); ?>
                                </div>
                            <?php else: ?>
                                <div class="col-sm-12" id="mandataire_choice">
                                    <?php echo $form['mandataire_identifiant']->render(array('class' => 'form-control select2autocomplete select-ajax', 'placeholder' => 'Sélectionner un mandataire', 'data-url' => url_for('vrac_soussigne_getinfos'), 'data-bloc' => '#mandataire_informations', 'data-hide' => '#mandataire_selection')); ?>
                                </div>
                              <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-12 text-center <?php if(!$form['mandataire_identifiant']->getValue()): ?>hidden<?php endif; ?>" id="mandataire_informations">
                            <div class="pull-right">
                            <button type="button" class="btn btn-xs btn-default select-close" data-select="#<?php echo $form['mandataire_identifiant']->renderId() ?>"><span class="glyphicon glyphicon-remove"></span></button>
                            <span>&nbsp;<?php echo getPointAideHtml('vrac','soussigne_courtier_supprimer'); ?></span>
                            </div>
                            <div class="container-ajax">
                              <?php if($form['mandataire_identifiant']->getValue()): ?>
                              <?php include_partial('vrac/soussigne', array('id' => $form['mandataire_identifiant']->getValue(), 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>
                              <?php endif; ?>
                            </div>
                        </div>
                        <?php if (isset($form['commercial'])): ?>
                        <div class="col-sm-12">
                            <?php echo $form['commercial']->renderError(); ?>
                            <div class="form-group <?php if($form['commercial']->hasError()): ?>has-error<?php endif; ?>">
                                <?php echo $form['commercial']->renderLabel("Mandaté par :", array('class' => 'col-sm-2 control-label')); ?>
                                <div class="col-sm-10">
                                    <?php echo $form['commercial']->render(array('class' => 'form-control')); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-4 col-md-push-8 text-right">
            <button type="submit" class="btn btn-success">Étape suivante <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
        <div class="col-xs-4 text-center">
            <?php if ($vrac->isBrouillon()) : ?>
                <a tabindex="-1" class="btn btn-danger" href="<?php echo url_for('vrac_supprimer_brouillon', $vrac); ?>"><span class="glyphicon glyphicon-trash"></span> Supprimer le brouillon</a>
            <?php endif; ?>
        </div>

        <div class="col-xs-4 col-md-pull-8 text-left">
            <?php if ($isTeledeclarationMode): ?>
                <a tabindex="-1" href="<?php echo url_for('vrac_societe', array('identifiant' => $etablissementPrincipal->identifiant)); ?>" class="btn btn-default">Suspendre la saisie</a>
            <?php else: ?>
                <button type="submit" name="precedent" value="1" tabindex="-1" class="btn btn-default">Suspendre la saisie</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($isTeledeclarationMode && $vrac->isVendeurResponsable()): ?>
        <?php echo $form['isVendeur']->render(); ?>
    <?php endif; ?>
</form>
</section>

<?php
use_helper('Float');
use_helper('Vrac');
use_helper('PointsAides');
?>

<?php
$etablissementPrincipal = (isset($etablissementPrincipal))? $etablissementPrincipal : null;
include_partial('vrac/breadcrumbSaisie', array('vrac' => $vrac, 'isTeledeclarationMode' => $isTeledeclarationMode, 'etablissementPrincipal' => $etablissementPrincipal)); ?>

<section id="principal" class="vrac">

<?php include_component('vrac', 'etapes', array('vrac' => $form->getObject(), 'compte' => $compte, 'actif' => 3, 'urlsoussigne' => null, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<form action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
    <div class="row">
        <div class="col-sm-12">
        	<p>
            	<span class="<?php echo typeToPictoCssClass($vrac->type_transaction) ?>" style="font-size: 24px;"><?php echo "&nbsp;Contrat de " . showType($vrac); ?></span>
            </p>
        	<div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><label>Paiement</label></h3>
                </div>
                <div class="panel-body">
		        	<?php if (isset($form['delai_paiement'])): ?>
		            <div class="form-group col-sm-6 <?php if($form['delai_paiement']->hasError()): ?>has-error<?php endif; ?>">
		                <?php echo $form['delai_paiement']->renderError(); ?>
                    <div class="col-sm-1 pull-right" style="top:-20px;" ><?php echo getPointAideHtml('vrac','condition_delais_paiement'); ?></div>
		                <?php echo $form['delai_paiement']->renderLabel("Délai de paiement :", array('class' => 'col-sm-4 control-label')); ?>
		                <div class="col-sm-7">
		                    <?php echo $form['delai_paiement']->render(array('autofocus' => 'autofocus')); ?>
		                </div>
		            </div>
		            <?php endif; ?>
		        	<?php if (isset($form['acompte'])): ?>
		            <div class="form-group col-sm-6 <?php if($form['acompte']->hasError()): ?>has-error<?php endif; ?>">
		                <?php echo $form['acompte']->renderError(); ?>
                        <div class="col-sm-1 pull-right" style="top:-20px;" ><?php echo getPointAideHtml('vrac','condition_accompte'); ?></div>
		                <?php echo $form['acompte']->renderLabel("Acompte à la signature :", array('class' => 'col-sm-6 control-label')); ?>
		                <div class="col-sm-5 pull-right">
							<div class="input-group">
		                    	<?php echo $form['acompte']->render(); ?>
								<span class="input-group-addon">&nbsp;€&nbsp;&nbsp;</span>
							</div>
		                </div>
		            </div>
		        	<?php endif; ?>
		        	<?php if (isset($form['moyen_paiement'])): ?>
		            <div class="form-group col-sm-6 <?php if($form['moyen_paiement']->hasError()): ?>has-error<?php endif; ?>">
		                <?php echo $form['moyen_paiement']->renderError(); ?>
                    <div class="col-sm-1 pull-right" style="top:-20px;" ><?php echo getPointAideHtml('vrac','condition_moyen_paiement'); ?></div>
		                <?php echo $form['moyen_paiement']->renderLabel("Moyen de paiement :", array('class' => 'col-sm-5 control-label')); ?>
		                <div class="col-sm-6">
		                    <?php echo $form['moyen_paiement']->render(); ?>
		                </div>
		            </div>
		            <?php endif; ?>
		        	<?php if (isset($form['tva'])): ?>
		            <div class="form-group col-sm-6 <?php if($form['tva']->hasError()): ?>has-error<?php endif; ?>">
		                <?php echo $form['tva']->renderError(); ?>
                    <div class="col-sm-1 pull-right" style="top:-20px;" ><?php echo getPointAideHtml('vrac','condition_hors_tva'); ?></div>
		                <?php echo $form['tva']->renderLabel("Facturation :", array('class' => 'col-sm-4 control-label')); ?>
		                <div class="col-sm-7">
		                    <?php echo $form['tva']->render(); ?>
		                </div>
		            </div>
		            <?php endif; ?>
		            <div class="col-sm-12"></div>
                    <?php if ($vrac->mandataire_exist && isset($form['courtage_taux'])): ?>
		            <div class="form-group col-sm-6 <?php if($form['courtage_taux']->hasError()): ?>has-error<?php endif; ?>">
		                <?php echo $form['courtage_taux']->renderError(); ?>
                        <div class="col-sm-1 pull-right" style="top:-20px;" ><?php echo getPointAideHtml('vrac','condition_taux_courtage'); ?></div>
		                <?php echo $form['courtage_taux']->renderLabel("Taux de courtage :", array('class' => 'col-sm-4 control-label')); ?>
		                <div class="col-sm-7">
							<div class="input-group">
		                    	<?php echo $form['courtage_taux']->render(); ?>
								<span class="input-group-addon">&nbsp;%&nbsp;&nbsp;</span>
							</div>
		                </div>
		            </div>
		            <?php endif; ?>
		        	<?php if (isset($form['courtage_repartition']) && $vrac->mandataire_exist): ?>
		            <div class="form-group col-sm-6 <?php if($form['courtage_repartition']->hasError()): ?>has-error<?php endif; ?>">
		                <?php echo $form['courtage_repartition']->renderError(); ?>
                    <div class="col-sm-1 pull-right" style="top:-20px;" ><?php echo getPointAideHtml('vrac','condition_repartition_courtage'); ?></div>
		                <?php echo $form['courtage_repartition']->renderLabel("Répartition du courtage :", array('class' => 'col-sm-5 control-label')); ?>
		                <div class="col-sm-6">
		                    <?php echo $form['courtage_repartition']->render(); ?>
		                </div>
		            </div>
		            <?php endif; ?>
                </div>
           </div>

        	<div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><label>Retiraison</label></h3>
                </div>
                <div class="panel-body">
                    <div class="col-sm-6">
		        	<?php if (isset($form['date_debut_retiraison'])): ?>
		            <div class="form-group  <?php if($form['date_debut_retiraison']->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form['date_debut_retiraison']->renderError(); ?>
                        <?php echo $form['date_debut_retiraison']->renderLabel("Date début de retiraison :", array('class' => 'col-sm-5 control-label')); ?>
                        <div class="col-sm-7">
                            <?php echo $form['date_debut_retiraison']->render(array('placeholder' => 'jj/mm/aaaa')); ?>
                        </div>
		            </div>
		            <?php endif; ?>
		        	<?php if (isset($form['date_limite_retiraison'])): ?>
		            <div class="form-group  <?php if($form['date_limite_retiraison']->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form['date_limite_retiraison']->renderError(); ?>
                        <div class="col-sm-1 pull-right" style="top:-20px;" ><?php echo getPointAideHtml('vrac','condition_date_retiraison'); ?></div>
                        <?php echo $form['date_limite_retiraison']->renderLabel("Date limite de retiraison :", array('class' => 'col-sm-5 control-label')); ?>
                        <div class="col-sm-6">
                            <?php echo $form['date_limite_retiraison']->render(array('placeholder' => 'jj/mm/aaaa')); ?>
                        </div>
		            </div>
		            <?php endif; ?>
                    </div>
                    <div class="col-sm-6">
		            <?php if(isset($form['clause_reserve_propriete'])): ?>
						<div class="form-group">
							<?php echo $form['clause_reserve_propriete']->renderError(); ?>
                            <div class="col-sm-1 col-sm-offset-5">
                            </div>
							<div class="checkbox col-sm-6 ">
								<label for="<?php echo $form['clause_reserve_propriete']->renderId(); ?>">
									<?php echo $form['clause_reserve_propriete']->render(); ?>
									Clause de réserve de propriété
                                    &nbsp; <?php echo getPointAideHtml('vrac','condition_clause_reserve_prop'); ?>
                                    <br /><br />
								</label>
							</div>
						</div>
					<?php endif; ?>
                    </div>
                </div>
            </div>
           <?php if (isset($form['preparation_vin']) || isset($form['embouteillage']) || isset($form['conditionnement_crd'])): ?>
        	<div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><label>Embouteillage</label></h3>
                </div>
                <div class="panel-body">
		        	<?php if (isset($form['preparation_vin'])): ?>
		            <div class="form-group col-sm-6 <?php if($form['preparation_vin']->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form['preparation_vin']->renderError(); ?>
    		                <?php echo $form['preparation_vin']->renderLabel("Préparation du vin :", array('class' => 'col-sm-5 control-label')); ?>
                        <div class="col-sm-7">
                            <?php echo $form['preparation_vin']->render(); ?>
                        </div>
		            </div>
		            <?php endif; ?>
		        	<?php if (isset($form['embouteillage'])): ?>
		            <div class="form-group col-sm-6 <?php if($form['embouteillage']->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form['embouteillage']->renderError(); ?>
                        <?php echo $form['embouteillage']->renderLabel("Mise en bouteille :", array('class' => 'col-sm-5 control-label')); ?>
                        <div class="col-sm-7">
                            <?php echo $form['embouteillage']->render(); ?>
                        </div>
		            </div>
		            <?php endif; ?>
		        	<?php if (isset($form['conditionnement_crd'])): ?>
		            <div class="form-group col-sm-6 <?php if($form['conditionnement_crd']->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form['conditionnement_crd']->renderError(); ?>
                        <?php echo $form['conditionnement_crd']->renderLabel("Conditionnement CRD négoce apportées:", array('class' => 'col-sm-5 control-label')); ?>
                        <div class="col-sm-7">
                            <?php echo $form['conditionnement_crd']->render(); ?>
                        </div>
		            </div>
		            <?php endif; ?>
                </div>
            </div>
           <?php endif; ?>
        	<div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><label>Clauses</label></h3>
                </div>
                <div class="panel-body">
                	<div class="row col-sm-6 ">
			        	<?php if (isset($form['conditions_particulieres'])): ?>
			            <div class="<?php if($form['conditions_particulieres']->hasError()): ?>has-error<?php endif; ?>">
			                <?php echo $form['conditions_particulieres']->renderError(); ?>

                      <div class="col-sm-1" style="margin-top:-14px;" >
                      </div>
			                <?php echo $form['conditions_particulieres']->renderLabel("Observations :"); ?>
                            &nbsp; <?php echo getPointAideHtml('vrac','condition_observation'); ?>
			                <?php echo $form['conditions_particulieres']->render(); ?>
			            </div>
		            	<?php endif; ?>
		            </div>
                	<div class="row col-sm-6">
        				<?php if(isset($form['cahier_charge'])): ?>
							<div class="form-group <?php if($form['cahier_charge']->hasError()): ?>has-error<?php endif; ?>">
                                <div class="col-sm-10 col-sm-offset-2">
									<?php echo $form['cahier_charge']->renderError(); ?>

									<div class="checkbox">
										<label for="<?php echo $form['cahier_charge']->renderId(); ?>">
											<?php echo $form['cahier_charge']->render(); ?>
											Présence d'un cahier des charges entre le vendeur et l'acheteur
										</label>
									</div>
								</div>
							</div>
						<?php endif; ?>

		            <?php if(isset($form['autorisation_nom_vin'])): ?>
						<div class="form-group">
							<?php echo $form['autorisation_nom_vin']->renderError(); ?>
              	             <div class="checkbox col-sm-8 col-sm-offset-2">
								<label for="<?php echo $form['autorisation_nom_vin']->renderId(); ?>">
									<?php echo $form['autorisation_nom_vin']->render(); ?>
									Autorisation d'utilisation du nom du vin
								</label>
                                &nbsp; <?php echo getPointAideHtml('vrac','condition_nom_vin'); ?>
							</div>
						</div>
					<?php endif; ?>

		            <?php if(isset($form['autorisation_nom_producteur'])): ?>
						<div class="form-group">
							<?php echo $form['autorisation_nom_producteur']->renderError(); ?>
							<div class="checkbox col-sm-8 col-sm-offset-2">
								<label for="<?php echo $form['autorisation_nom_producteur']->renderId(); ?>">
									<?php echo $form['autorisation_nom_producteur']->render(); ?>
									Autorisation d'utilisation du nom du producteur
								</label>
                                &nbsp;<?php echo getPointAideHtml('vrac','condition_nom_producteur'); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
                </div>
            </div>

        	<?php if(isset($form['pluriannuel'])): ?>
            <?php echo $form['pluriannuel']->renderError(); ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                    	<span class="bloc_condition" data-condition-cible="#bloc_pluriannuel"  for="<?php echo $form['pluriannuel']->renderId(); ?>"><?php echo $form['pluriannuel']->render(); ?>&nbsp;&nbsp;<label for="vrac_pluriannuel">Contrat pluriannuel</label></span>
					</h3>
                </div>
                <div id="bloc_pluriannuel" data-condition-value="1" class="panel-body bloc_conditionner">
                    <div class="row col-sm-6">
                        <?php if(isset($form['annee_contrat'])): ?>
						<div class="form-group">
							<?php echo $form['annee_contrat']->renderError(); ?>
			                <?php echo $form['annee_contrat']->renderLabel("Année du contrat :", array('class' => 'col-sm-5 control-label')); ?>
							<div class="col-sm-7 bloc_condition" data-condition-cible="#bloc_reference|#bloc_seuil|#bloc_variation">
								<?php echo $form['annee_contrat']->render(); ?>
							</div>
						</div>
						<?php endif; ?>
					</div>
                    <div class="row col-sm-6">
		            	<?php if(isset($form['reference_contrat'])): ?>
						<div class="form-group" id="bloc_reference" data-condition-value="2|3">
							<?php echo $form['reference_contrat']->renderError(); ?>
			                <?php echo $form['reference_contrat']->renderLabel("Référence du contrat :", array('class' => 'col-sm-5 control-label')); ?>
							<div class="col-sm-7">
									<?php echo $form['reference_contrat']->render(); ?>
							</div>
						</div>
						<?php endif; ?>
		            	<?php if(isset($form['seuil_revision'])): ?>
						<div class="form-group" id="bloc_seuil" data-condition-value="1">
							<?php echo $form['seuil_revision']->renderError(); ?>
			                <?php echo $form['seuil_revision']->renderLabel("Seuil de révision du prix :", array('class' => 'col-sm-5 control-label')); ?>
							<div class="col-sm-7">
								<div class="input-group">
									<?php echo $form['seuil_revision']->render(); ?>
									<span class="input-group-addon">&nbsp;%&nbsp;&nbsp;</span>
								</div>
							</div>
						</div>
						<?php endif; ?>
		            	<?php if(isset($form['pourcentage_variation'])): ?>
						<div class="form-group" id="bloc_variation" data-condition-value="1">
							<?php echo $form['pourcentage_variation']->renderError(); ?>
			                <?php echo $form['pourcentage_variation']->renderLabel("Variation max. du volume :", array('class' => 'col-sm-5 control-label')); ?>
							<div class="col-sm-7">
								<div class="input-group">
									<?php echo $form['pourcentage_variation']->render(); ?>
									<span class="input-group-addon">&nbsp;%&nbsp;&nbsp;</span>
								</div>
							</div>
						</div>
						<?php endif; ?>
					</div>
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
            <?php if ($isTeledeclarationMode && $vrac->isBrouillon()) : ?>
                <a class="btn btn-default" href="<?php echo url_for('vrac_supprimer_brouillon', $vrac); ?>">Supprimer le brouillon</a>
              <?php endif; ?>
           <?php if (!$isTeledeclarationMode) : ?>
                <button type="submit" tabindex="-1" name="redirect" value="<?php echo url_for('vrac'); ?>" class="btn btn-default" ><span class="glyphicon glyphicon-floppy-disk"></span> Enregistrer en brouillon</button>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 col-md-pull-8 text-left">
            <button type="submit" tabindex="-1" name="redirect" value="<?php echo url_for('vrac_marche',$vrac); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Etape précédente</button>
        </div>
    </div>
</form>
</section>

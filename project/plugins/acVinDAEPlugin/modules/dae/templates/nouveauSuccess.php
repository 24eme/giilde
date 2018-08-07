<?php use_helper('Date'); ?>
<?php include_partial('dae/preTemplate'); ?>
<ol class="breadcrumb">
    <li><a href="<?php echo url_for('dae') ?>">Activités mensuelle</a></li>
    <li><a href="<?php echo url_for('dae_etablissement', array('identifiant' => $etablissement->identifiant)) ?>"><?php echo $etablissement->nom ?> (<?php echo $etablissement->identifiant ?>)</a></li>
    <li><a href="<?php echo url_for('dae_nouveau', array('identifiant' => $etablissement->identifiant)) ?>" class="active">Nouveau</a></li>
</ol>

<div class="col-md-12">

	<h4><?php if ($dae->isNew()): ?>Ajout<?php else: ?>Modification<?php endif; ?> d'une vente de <strong><?php echo ucfirst(format_date($periode->format('Y-m-d'), 'MMMM yyyy', 'fr_FR')) ?></strong></h4>
	
	<div class="col-md-12" style="margin-top: 20px;">
	
	<form class="form-horizontal" action="<?php echo ($dae->isNew())? url_for('dae_nouveau', array('identifiant' => $etablissement->identifiant, 'periode' => $periode->format('Y-m-d'))) : url_for('dae_nouveau', array('identifiant' => $etablissement->identifiant, 'id' => $dae->_id, 'periode' => $periode->format('Y-m-d'))); ?>" method="post">
		<?php
		    echo $form->renderHiddenFields();
		    echo $form->renderGlobalErrors();
	    ?>
		<div class="panel panel-default">
        	<div class="panel-heading"><h4 class="panel-title">Client</h4></div>
        	<div class="panel-body">
			    <div class="form-group">
				    <?php echo $form['type_acheteur_key']->renderError(); ?>
				    <?php echo $form['destination_key']->renderError(); ?>
				    <div class="<?php if($form['destination_key']->hasError()): ?> has-error<?php endif; ?>">
				        <?php echo $form['destination_key']->renderLabel(null, array("class" => "col-xs-1 control-label")); ?>
				        <div class="col-xs-5"><?php echo $form['destination_key']->render(); ?></div>
				    </div>
				    <div class="<?php if($form['type_acheteur_key']->hasError()): ?> has-error<?php endif; ?>">
				            <?php echo $form['type_acheteur_key']->renderLabel(null, array("class" => "col-xs-1 control-label")); ?>
				            <div class="col-xs-5"><?php echo $form['type_acheteur_key']->render(); ?></div>
				    </div>
			    </div>
			    
			    <div class="text-right">
			    	<a class="collapsed" role="button" data-toggle="collapse" href="javascript:void(0)" aria-expanded="false" aria-controls="clientDetails"><span class="glyphicon glyphicon-chevron-down"></span> détailler le client</a>
			    </div>
			    
			    <div class="form-group collapse" id="clientDetails"<?php if ($form['no_accises_acheteur']->hasError() || $form['nom_acheteur']->hasError()): ?> style="display: block;"<?php endif; ?>>
			    	<p>&nbsp;</p>
			    	<?php echo $form['no_accises_acheteur']->renderError(); ?>
			    	<?php echo $form['nom_acheteur']->renderError(); ?>
				    <div class="<?php if($form['no_accises_acheteur']->hasError()): ?> has-error<?php endif; ?>">
				        <?php echo $form['no_accises_acheteur']->renderLabel(null, array("class" => "col-xs-1 control-label")); ?>
				        <div class="col-xs-5"><?php echo $form['no_accises_acheteur']->render(); ?></div>
				    </div>
				    <div class="<?php if($form['nom_acheteur']->hasError()): ?> has-error<?php endif; ?>">
				            <?php echo $form['nom_acheteur']->renderLabel(null, array("class" => "col-xs-1 control-label")); ?>
				            <div class="col-xs-5"><?php echo $form['nom_acheteur']->render(); ?></div>
				    </div>
			    </div>
			</div>
		</div>
		
	    <div class="panel panel-default">
        	<div class="panel-heading"><h4 class="panel-title">Produit</h4></div>
        	<div class="panel-body">
        	
			    <div class="form-group">
			        <?php echo $form['produit_key']->renderError(); ?>
				    <?php echo $form['millesime']->renderError(); ?>
				    <div class="<?php if($form['produit_key']->hasError()): ?> has-error<?php endif; ?>">
				        <?php echo $form['produit_key']->renderLabel(null, array("class" => "col-xs-1 control-label")); ?>
				        <div class="col-xs-6"><?php echo $form['produit_key']->render(array()); ?></div>
			        </div>
			    	<div class="<?php if($form['millesime']->hasError()): ?> has-error<?php endif; ?>">
				        <div class="col-xs-5"><?php echo $form['millesime']->render(array('placeholder' => 'Millésime')); ?></div>
				    </div>
			    </div>
			
			    <div class="form-group">
				    <?php echo $form['label_key']->renderError(); ?>
				    <?php echo $form['mention_key']->renderError(); ?>
			    	<div class="<?php if($form['label_key']->hasError()): ?> has-error<?php endif; ?>">
				        <?php echo $form['label_key']->renderLabel(null, array("class" => "col-xs-1 control-label")); ?>
				        <div class="col-xs-5 bloc_condition" data-condition-cible="#bloc_label_libelle"><?php echo $form['label_key']->render(array()); ?></div>
			        </div>
			    	<div class="<?php if($form['mention_key']->hasError()): ?> has-error<?php endif; ?>">
				        <?php echo $form['mention_key']->renderLabel(null, array("class" => "col-xs-1 control-label")); ?>
				        <div class="col-xs-5"><?php echo $form['mention_key']->render(array()); ?></div>
			        </div>
			    </div>
        	
        		<div class="form-group<?php if($form['label_libelle']->hasError()): ?> has-error<?php endif; ?>" id="bloc_label_libelle" data-condition-value="AUTRE">
        			<?php echo $form['label_libelle']->renderError(); ?>
				    <?php echo $form['label_libelle']->renderLabel(null, array("class" => "col-xs-1 control-label")); ?>
				    <div class="col-xs-5"><?php echo $form['label_libelle']->render(); ?></div>
			    </div>
			</div>
			
			<div class="panel-heading" style="border-top: 1px solid #ddd; border-top-left-radius: 0px; border-top-right-radius: 0px;"><h4 class="panel-title">Marché</h4></div>
			
			<div class="panel-body">
		    
		    <div class="form-group">
		    	<?php echo $form['contenance_key']->renderError(); ?>
			    <?php echo $form['quantite']->renderError(); ?>
			    <?php echo $form['prix_unitaire']->renderError(); ?>
			    <div class="<?php if($form['contenance_key']->hasError()): ?> has-error<?php endif; ?>">
			        <?php echo $form['contenance_key']->renderLabel(null, array("class" => "col-xs-1 control-label")); ?>
			   		<div class="col-xs-6 bloc_condition" data-condition-cible="#bloc_hl|#bloc_btl|#bloc_bib|#bloc_euro_hl|#bloc_euro_btl|#bloc_euro_bib"><?php echo $form['contenance_key']->render(); ?></div>
			    </div>
			    <div class="col-xs-5">
			        <div class="input-group col-xs-5 <?php if($form['quantite']->hasError()): ?> has-error<?php endif; ?>">
			        	<?php echo $form['quantite']->render(array('placeholder' => 'Quantité')); ?>
			        	<div class="input-group-addon" id="bloc_hl" data-condition-value="HL" style="border-top-right-radius: 4px; border-bottom-right-radius: 4px; border-left: 0px;">hl</div>
			        	<div class="input-group-addon" id="bloc_btl" data-condition-value="<?php $i=0; $nb = count($form->getContenances()); foreach ($form->getContenances() as $k => $v) {$i++; if (preg_match('/CL_/', $k)) { echo $k; if ($i<$nb) { echo '|'; } } } ?>" style="border-top-right-radius: 4px; border-bottom-right-radius: 4px; border-left: 0px;">btl</div>
			        	<div class="input-group-addon" id="bloc_bib" data-condition-value="<?php $i=0; $nb = count($form->getContenances()); foreach ($form->getContenances() as $k => $v) {$i++; if (preg_match('/BIB_/', $k)) { echo $k; if ($i<$nb) { echo '|'; } } } ?>" style="border-top-right-radius: 4px; border-bottom-right-radius: 4px; border-left: 0px;">bib</div>
			        </div>
			        <div class="input-group col-xs-6 col-xs-offset-1 <?php if($form['prix_unitaire']->hasError()): ?> has-error<?php endif; ?>">
			        	<?php echo $form['prix_unitaire']->render(array('placeholder' => 'Prix unitaire')); ?>
			        	<div class="input-group-addon" id="bloc_euro_hl" data-condition-value="HL" style="border-top-right-radius: 4px; border-bottom-right-radius: 4px; border-left: 0px;">€/hl</div>
			        	<div class="input-group-addon" id="bloc_euro_btl" data-condition-value="<?php $i=0; $nb = count($form->getContenances()); foreach ($form->getContenances() as $k => $v) {$i++; if (preg_match('/CL_/', $k)) { echo $k; if ($i<$nb) { echo '|'; } } } ?>" style="border-top-right-radius: 4px; border-bottom-right-radius: 4px; border-left: 0px;">€/btl</div>
			        	<div class="input-group-addon" id="bloc_euro_bib" data-condition-value="<?php $i=0; $nb = count($form->getContenances()); foreach ($form->getContenances() as $k => $v) {$i++; if (preg_match('/BIB_/', $k)) { echo $k; if ($i<$nb) { echo '|'; } } } ?>" style="border-top-right-radius: 4px; border-bottom-right-radius: 4px; border-left: 0px;">€/bib</div>
			        </div>
			    </div>
		    </div>
		    
		    <p class="text-right"><button id="btn_valider" type="submit" class="btn btn-warning btn-sm">Valider & nouveau produit</button></p>
			
			</div>
		</div>
		


		<div class="col-xs-6"><a href="<?php echo url_for('dae_etablissement', array('identifiant' => $etablissement->identifiant)) ?>" class="btn btn-default">Annuler</a></div>
	    <div class="col-xs-6 text-right"><button id="btn_valider" type="submit" class="btn btn-success">Valider & terminer</button></div>
	    
	</form>
	
	</div>
	
</div>
<?php include_partial('dae/postTemplate'); ?>
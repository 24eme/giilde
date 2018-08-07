<?php use_helper('Date') ?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('fichiers') ?>">Fichiers</a></li>
    <li><a href="<?php echo url_for('fichiers_etablissement', array('identifiant' => $etablissement->identifiant)) ?>"><?php echo $etablissement->nom ?> (<?php echo $etablissement->identifiant ?>)</a></li>
    <li class="active"><a href="">Modifier</a></li>
</ol>

<div class="page-header">
    <h2>Modification du document <span><small><?php echo $fichier->libelle ?></small></span></h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>

<form class="form-horizontal" role="form" action="<?php echo url_for("edit_fichier", $fichier) ?>" method="post" novalidate>
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    
    <div id="drLignes">
   		<?php 
   		foreach ($form['donnees'] as $key => $documentForm): 
   			include_partial('form_dr_ligne', array('form' => $form['donnees'][$key]));
		endforeach;
    	?>
	</div>
	<div class="row row-margin row-button" style="margin-top: 10px; margin-bottom: 20px;">
		<div class="col-xs-12 pull-right">
			<a href="javascript:void(0)" role="button" class="text-success pull-right btn_ajouter_ligne_template" data-callback="1" data-container="#drLignes" data-template="#template_drLignesForm">Ajouter une ligne&nbsp;<span class="glyphicon glyphicon-plus-sign" style="font-size: 20px"></span></a>
			<script id="template_drLignesForm" type="text/x-jquery-tmpl">
				<?php echo include_partial('form_dr_ligne', array('form' => $form->getFormTemplateDrLigne())); ?>
			</script>
		</div>
	</div>
    

    <div class="row row-margin row-button">
        <div class="col-xs-6">
        	<a href="<?php echo url_for('fichiers_etablissement', $etablissement) ?>" class="annuler btn btn-default btn-danger">Retour</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Valider</button>
        </div>
    </div>
</form>

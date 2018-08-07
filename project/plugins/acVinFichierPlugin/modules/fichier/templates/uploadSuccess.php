<?php use_helper('Date') ?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('fichiers') ?>">Fichiers</a></li>
    <li><a href="<?php echo url_for('fichiers_etablissement', array('identifiant' => $etablissement->identifiant)) ?>"><?php echo $etablissement->nom ?> (<?php echo $etablissement->identifiant ?>)</a></li>
    <li class="active"><a href=""><?php if($fichier->isNew()): ?>Ajouter<?php else: ?>Modifier<?php endif; ?></a></li>
</ol>

<div class="page-header">
    <h2><?php if($fichier->isNew()): ?>Ajout de document<?php else: ?>Modification du document<?php endif; ?></a></h2>
</div>

<form class="form-horizontal" role="form" action="<?php echo url_for("upload_fichier", array('fichier_id' => $fichier_id, 'sf_subject' => $etablissement)) ?>" method="post" enctype="multipart/form-data">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row">
    	<div class="form-group <?php if($form['libelle']->hasError()): ?>has-error<?php endif; ?>">
			<div class="col-xs-8 col-xs-offset-4">
				<?php echo $form['libelle']->renderError() ?>
			</div>
			<div class="col-xs-3 col-xs-offset-1">
				<?php echo $form['libelle']->renderLabel() ?>
			</div>
			<div class="col-xs-6">
				<?php echo $form['libelle']->render(array('class' => 'form-control input', 'placeholder' => "Libellé du document")) ?>
			</div>
		</div>
    </div>

    <div class="row">
    	<div class="form-group <?php if($form['date_depot']->hasError()): ?>has-error<?php endif; ?>">
			<div class="col-xs-8 col-xs-offset-4">
				<?php echo $form['date_depot']->renderError() ?>
			</div>
			<div class="col-xs-3 col-xs-offset-1">
				<?php echo $form['date_depot']->renderLabel() ?>
			</div>
			<div class="col-xs-6">
				<div class="input-group date">
					<?php echo $form['date_depot']->render(array('class' => 'form-control', 'placeholder' => "Date du dépôt")); ?>
					<div class="input-group-addon">
						<span class="glyphicon-calendar glyphicon"></span>
					</div>
				</div>
			</div>
		</div>
    </div>

    <div class="row">
    	<div class="form-group <?php if($form['visibilite']->hasError()): ?>has-error<?php endif; ?>">
			<div class="col-xs-8 col-xs-offset-4">
				<?php echo $form['visibilite']->renderError() ?>
			</div>
			<div class="col-xs-3 col-xs-offset-1">
				<?php echo $form['visibilite']->renderLabel() ?>
			</div>
			<div class="col-xs-6">
				<?php echo $form['visibilite']->render() ?>
			</div>
		</div>
    </div>

    <div class="row">
    	<div class="form-group <?php if($form['file']->hasError()): ?>has-error<?php endif; ?>">
			<div class="col-xs-8 col-xs-offset-4">
				<?php echo $form['file']->renderError() ?>
			</div>
			<div class="col-xs-3 col-xs-offset-1">
				<?php echo $form['file']->renderLabel() ?>
			</div>
			<div class="col-xs-4">
				<?php echo $form['file']->render() ?>
			</div>
			<div class="col-xs-2">
				<button name="keep_page" type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span></button>
			</div>
		</div>
    </div>

    <div class="row">
    	<div class="form-group ">
    		<div class="col-xs-8 col-xs-offset-4">
		    	<?php foreach ($fichier->_attachments as $key => $file): ?>
		    	<?php
		    		$infos = explode('.', $key);
		    		$extention = (isset($infos[1]))? $infos[1] : "";
		    	?>
		    	<div class="btn-group" role="group">
		    		<a href="<?php echo $fichier->generateUrlPiece() ?>?file=<?php echo $key ?>" class="btn btn-default"><span class="glyphicon glyphicon-file"></span>&nbsp;<?php echo strtoupper($extention); ?></a>
		    		<a class="btn btn-danger" href="<?php echo url_for('delete_fichier', $fichier) ?>?file=<?php echo $key ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?');"><strong>X</strong></a>
		    	</div>
		    	<?php endforeach; ?>
    		</div>
    	</div>
    </div>
	
		<?php if ($urlCsv = Piece::getUrlGenerationCsvPiece($fichier->_id, false)): ?>
		<div class="row row-margin row-button text-center">
			<a class="btn btn-default" href="<?php echo $urlCsv ?>" style="margin: 0 10px;"><span class="glyphicon glyphicon-file"></span> CSV Généré</a>
		</div>
		<?php endif; ?>
    <div class="row row-margin row-button">
        <div class="col-xs-6">
        	<a href="<?php echo url_for('fichiers_etablissement', $etablissement) ?>" class="annuler btn btn-default btn-danger">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper"><?php if($fichier->isNew()): ?>Ajouter<?php else: ?>Modifier<?php endif; ?></button>
        </div>
    </div>
</form>

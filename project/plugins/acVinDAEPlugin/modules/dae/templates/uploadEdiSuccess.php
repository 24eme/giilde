<?php use_helper('Date'); ?>
<?php include_partial('dae/preTemplate'); ?>
<?php include_partial('dae/breadcrum', array('etablissement' => $etablissement)); ?>

<div class="col-md-12">

	<h4>Import des ventes de <strong><?php echo ucfirst(format_date($periode->format('Y-m-d'), 'MMMM yyyy', 'fr_FR')) ?></strong></h4>

	<div class="col-md-12" style="margin-top: 20px;">

	<form class="form-horizontal" action="<?php echo url_for('dae_upload_fichier_edi', array('identifiant' => $identifiant, 'periode' => $periode->format('Y-m-d'),'md5' => "0")); ?>" method="post" enctype="multipart/form-data">
		<?php
		    echo $form->renderHiddenFields();
		    echo $form->renderGlobalErrors();
	    ?>
		<div class="panel panel-default">
        	<div class="panel-heading"><h4 class="panel-title">Import depuis votre logiciel (DTI+)</h4></div>
        	<div class="panel-body">
				<div class="form-group <?php if($form['file']->hasError()): ?>has-error<?php endif; ?>">
					<?php echo $form['file']->renderError() ?>
					<?php echo $form['file']->renderLabel(null, array("class" => "col-xs-2 control-label")); ?>
					<div class="col-xs-5">
						<?php echo $form['file']->render() ?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-6"><a href="<?php echo url_for('dae_etablissement', array('identifiant' => $etablissement->identifiant)) ?>" class="btn btn-default">Annuler</a></div>
		<div class="col-xs-6 text-right"><button id="btn_valider" type="submit" class="btn btn-success">Importer vos ventes</button></div>
    </form>
    
    </div>
    
    <?php if ($md5 && count($erreurs) > 0): ?>
    	<p>&nbsp;</p>
    	<h4>Erreurs</h4>

		<div class="col-md-12" style="margin-top: 20px;">
				<table class="table table-bordered table-condensed table-striped">
					<thead>
						<tr>
							<th>Numéro de ligne</th>
							<th>Erreur</th>
							<th>Raison</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($erreurs as $erreur) : ?>
						<tr class="<?php echo ($daeCsvEdi->getCsvDoc()->getStatut() == DRMCsvEdi::STATUT_ERREUR) ? "danger" : "warning"; ?>">
							<td><?php echo $erreur->num_ligne; ?></td>
							<td><?php echo $erreur->csv_erreur; ?></td>
							<td><?php echo $erreur->diagnostic; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
		</div>
    <?php endif; ?>

</div>
<?php include_partial('dae/postTemplate'); ?>

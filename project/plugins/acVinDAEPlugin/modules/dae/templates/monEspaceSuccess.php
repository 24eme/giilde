<?php use_helper('Date'); ?>
<?php include_partial('dae/preTemplate'); ?>
<?php include_partial('dae/breadcrum', array('etablissement' => $etablissement)); ?>

<div class="row">
    <div class="col-xs-12" id="daeFormEtablissement">
        <?php include_component('dae', 'formEtablissementChoice', array('identifiant' => $etablissement->_id)) ?>
    </div>
</div>

<h1>Expérimentation des données de commercialisation</h1>


    <div class="col-xs-12">
    	<div class="row"><a class="btn btn-sm btn-default pull-right" href="<?php echo url_for('dae_export_edi', array('sf_subject' => $etablissement, 'campagne' => $campagne)) ?>"><span class=" glyphicon glyphicon-cloud-download"></span> Export des ventes <?php echo $campagne ?></a></div>
        <div class="row">
        	<h4>
        		Liste des ventes de <strong><?php echo ucfirst(format_date($periode->format('Y-m-d'), 'MMMM yyyy', 'fr_FR')) ?></strong>
	            <form class="form-inline pull-right" method="get">
	            	<?php echo $formCampagne->renderGlobalErrors() ?>
	                <?php echo $formCampagne->renderHiddenFields() ?>
				    <div class="form-group<?php if($formCampagne['periode']->hasError()): ?> has-error<?php endif; ?>">
				        <?php echo $formCampagne['periode']->renderError(); ?>
				        <?php echo $formCampagne['periode']->render(); ?>
				    </div>
				    <button type="submit" class="btn btn-default">Changer</button>
	            </form>
            </h4>
        </div>

        <p>&nbsp;</p>

        <div class="row form-horizontal">
        	<div class="form-group">
        		<label class="col-xs-1 control-label" for="quickfind">Filtres</label>
        		<div class="col-xs-5">
	        		<input type="text" id="quickfind" class="input-sm form-control" placeholder="Filtrer les ventes" />
	        	</div>
	        </div>
    	</div>

        <div class="row">
        	<?php include_partial('dae/recap', array('etablissement' => $etablissement, 'periode' => $periode, 'daes' => $daes)); ?>
        </div>
    </div>

	<div class="col-xs-6">
        <div class="row text-right">
        	<a class="btn btn-default" href="<?php echo url_for('dae_upload_fichier_edi', array('identifiant' => $etablissement->identifiant, 'periode' => $periode->format('Y-m-d'), 'md5' => "0")); ?>"><span class="glyphicon glyphicon-cloud-upload"></span> Importer</a>
        </div>
    </div>
	<div class="col-xs-6">
        <div class="row text-right">
			<a class="btn btn-default" href="<?php echo url_for('dae_nouveau', array('identifiant' => $etablissement->identifiant, 'periode' => $periode->format('Y-m-d')))?>"><span class="glyphicon glyphicon-plus-sign"></span> Ajouter</a>
		</div>
	</div>


	<script type="text/javascript">
	$('.table-filter').tableFilter({additionalFilterTriggers: [$('#quickfind')]});
	</script>
<?php use_javascript('lib/picnet.table.filter.min.js') ?>
<?php include_partial('dae/postTemplate'); ?>

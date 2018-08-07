<?php use_helper('Date'); ?>
<?php include_partial('dae/preTemplate'); ?>
<ol class="breadcrumb">
    <li><a href="<?php echo url_for('dae') ?>">Activités mensuelle</a></li>
    <li><a href="<?php echo url_for('dae_etablissement', array('identifiant' => $etablissement->identifiant)) ?>"><?php echo $etablissement->nom ?> (<?php echo $etablissement->identifiant ?>)</a></li>
    <li><a href="<?php echo url_for('dae_etablissement', array('identifiant' => $etablissement->identifiant)) ?>" class="active">XX</a></li>
</ol>

<div class="row">
    <div class="col-xs-12" id="daeFormEtablissement">
        <?php include_component('dae', 'formEtablissementChoice', array('identifiant' => $etablissement->_id)) ?>
    </div>
</div>

<h1>Expérimentation des données de commercialisation</h1>


    <div class="col-xs-12">
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
        <div class="row">
        	<?php include_partial('dae/recap', array('etablissement' => $etablissement, 'periode' => $periode, 'daes' => $daes)); ?>
        </div>
    </div>
    
	<div class="col-xs-6">
        <div class="row text-right">
        	<a class="btn btn-default" href="#"><span class="glyphicon glyphicon-download-alt"></span> Importer</a>
        </div>
    </div>
	<div class="col-xs-6">
        <div class="row text-right">
			<a class="btn btn-default" href="<?php echo url_for('dae_nouveau', array('identifiant' => $etablissement->identifiant, 'periode' => $periode->format('Y-m-d')))?>"><span class="glyphicon glyphicon-plus-sign"></span> Ajouter</a>
		</div>
	</div>

<?php include_partial('dae/postTemplate'); ?>
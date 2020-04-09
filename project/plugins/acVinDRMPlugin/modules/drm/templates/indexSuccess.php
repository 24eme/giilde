<ol class="breadcrumb">
    <li><a href="<?php echo url_for('drm') ?>" class="active">DRM</a></li>
</ol>

<div class="row">
    <div class="col-xs-12">
        <?php include_component('drm', 'formEtablissementChoice') ?>
    </div>
</div>
<?php $drm_controles = DRMClient::getDRMControles(); if(!empty($drm_controles)) : ?>
<div class="row col-xs-12">
    <h2>Liste des DRM ayant des points d'attention</h2>
    <table class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
                <th class="text-center col-xs-2">Période</th>
                <th class="text-center col-xs-2">Date de modification</th>
                <th class="text-center col-xs-5">Établissement</th>
                <th class="text-center col-xs-2">Controles</th>
                <th class="text-center col-xs-1"></th>
            </tr>
		</thead>
		<tbody>
		<?php foreach ($drm_controles as $identifiant => $drm_controle): ?>
            <tr>
            	<td class="text-center" ><a href="<?php $redirect = is_null($drm_controle->doc['valide']['date_saisie']) ? "drm_validation" : "drm_visualisation"; echo url_for($redirect, array('identifiant' => $identifiant, 'periode_version' => $drm_controle->doc['periode'])) ?>"><?php $periode = $drm_controle->doc["periode"]; echo substr($periode,-2)."-".substr($periode, 0, 4); ?></a></td>
                <td class="text-center"><?php echo count($drm_controle->doc["editeurs"]) ? " (".$drm_controle->doc['editeurs'][0]['date_modification']. ")":null; ?></td>                    
                <td class="text-left">
                    <a href="<?php echo url_for('drm_etablissement', array('identifiant' => $identifiant)) ?>">
                        <?php echo $drm_controle->doc["societe"]["raison_sociale"]." ($identifiant)"; ?>
                        <span class="text-muted small"><?php echo $drm_controle->doc["declarant"]["no_accises"]; ?></span>
                    </a>
                </td>
                <td class="text-center">
                    <?php foreach (array_keys($drm_controle->doc["controles"]) as $key => $controle): ?>
                        <span><?php echo $controle != DRM::TRANSMISSION ? "$controle; ": "Erreur $controle; "; ?></span>
                    <?php endforeach; ?>
                </td>
                <td class="text-center"><a class="btn btn-sm btn-default" href="<?php echo url_for($redirect, array('identifiant' => $identifiant, 'periode_version' => $drm_controle->doc['periode'])) ?>">Visualiser</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
	</table>
</div>
<?php endif; ?>
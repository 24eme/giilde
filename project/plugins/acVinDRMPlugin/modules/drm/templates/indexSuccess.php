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
                <th class="text-center col-xs-2">Période (Date de modification)</th>
                <th class="text-center col-xs-1">Étape</th>
                <th class="text-center col-xs-5">Établissement</th>
                <th class="text-center col-xs-2">Controles</th>
                <th class="text-center col-xs-1">Nombre de controles</th>
                <th class="text-center col-xs-1">Visualisation</th>
            </tr>
		</thead>
		<tbody>
		<?php foreach ($drm_controles as $identifiant => $drm_controle): ?>
            <tr>
            	<td class="text-left" ><a href="<?php echo url_for('drm_visualisation', array('identifiant' => $identifiant, 'periode_version' => $drm_controle->doc['periode'])) ?>"><?php $periode = $drm_controle->doc["periode"]; echo substr($periode,-2)."-".substr($periode, 0, 4); ?><span class="text-muted small"><?php echo count($drm_controle->doc["editeurs"]) ? " (".$drm_controle->doc['editeurs'][0]['date_modification']. ")":null; ?></span></a></td>
                <td class="text-center"><?php echo $drm_controle->doc["etape"]; ?></td>                    
                <td class="text-left">
                    <?php echo $drm_controle->doc["societe"]["raison_sociale"]." ($identifiant)"; ?>
                    <span class="text-muted small"><?php echo $drm_controle->doc["declarant"]["no_accises"]; ?></span>
                </td>
                <td>
                    <ul>
                    <?php foreach (array_keys($drm_controle->doc["controles"]) as $key => $controle): ?>
                        <li><?php echo "$controle"; ?></li>
                    <?php endforeach; ?>
                    </ul>
                </td>
                <td class="text-center"><?php echo DRMClient::getNbControlesDRM($drm_controle->doc["controles"]);?></td>
                <td class="text-center"><a class="btn btn-sm btn-default" href="<?php echo url_for('drm_visualisation', array('identifiant' => $identifiant, 'periode_version' => $drm_controle->doc['periode'])) ?>">Visualiser</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
	</table>
</div>
<?php endif; ?>
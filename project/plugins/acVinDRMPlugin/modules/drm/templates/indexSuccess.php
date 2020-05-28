<ol class="breadcrumb">
    <li><a href="<?php echo url_for('drm') ?>" class="active">DRM</a></li>
</ol>

<div class="row">
    <div class="col-xs-12">
        <?php include_component('drm', 'formEtablissementChoice') ?>
    </div>
</div>
<?php if($nb_results) : ?>    
<div class="row col-xs-12">
    <h2>Liste des DRM ayant des points d'attention</h2>
    <table class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
                <th class="text-center col-xs-2">Période</th>
                <th class="text-center col-xs-2">Date</th>
                <th class="text-center col-xs-5">Détail</th>
                <th class="text-center col-xs-2">Controles</th>
                <th class="text-center col-xs-1"></th>
            </tr>
		</thead>
		<tbody>
		<?php foreach ($drm_controles as $identifiant => $drm_controle): ?>
            <tr>
            	<td class="text-center" ><a href="<?php $redirect = is_null($drm_controle->doc['valide']['date_saisie']) ? "drm_validation" : "drm_visualisation"; echo url_for($redirect, array('identifiant' => $drm_controle->doc['identifiant'], 'periode_version' => $drm_controle->doc['periode'])) ?>"><?php echo $drm_controle->doc["periode"]; ?></a></td>
                <td class="text-center"><?php if(is_null($drm_controle->doc['valide']['date_saisie'])){echo $drm_controle->doc['date_modification'];}else{echo $drm_controle->doc['valide']['date_saisie'];}?></td>                    
                <td class="text-left">
                    <a href="<?php echo url_for($redirect, array('identifiant' => $drm_controle->doc['identifiant'], 'periode_version' => $drm_controle->doc['periode'])) ?>">
                        <?php echo $drm_controle->doc["societe"]["raison_sociale"]." (".$drm_controle->doc['identifiant'].")"; ?>
                        <span class="text-muted small"><?php echo $drm_controle->doc["declarant"]["no_accises"]; ?></span>
                    </a>
                </td>
                <td class="text-center">
                    <?php foreach ($drm_controle->doc["controles"] as $controle => $controleValue): ?>
                        <span><?php echo $controle != DRM::CONTROLE_TRANSMISSION ? "$controle; ": "Erreur de $controle; "; ?></span>
                    <?php endforeach; ?>
                </td>
                <td class="text-center"><a class="btn btn-sm btn-default" href="<?php echo url_for($redirect, array('identifiant' => $drm_controle->doc['identifiant'], 'periode_version' => $drm_controle->doc['periode'])) ?>">
                    <?php if($redirect == "drm_visualisation"): ?>Visualiser
                    <?php else: ?>Editer
                    <?php endif; ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
	</table>
    <h4>Nombre total : <?php echo $nb_results; ?></h4>
</div>
<div class="text-center">
        <nav>
            <ul class="pagination">
                <?php if ($current_page > 1) : ?>
                    <li><a href="<?php $args['page']=1; echo url_for('drm', $args); ?>"><span aria-hidden="true"><<</span></a></li>
                    <?php if ($current_page > 1) $args['page'] = $current_page - 1; ?>
                    <li><a href="<?php echo url_for('drm', $args); ?>"><span aria-hidden="true"><</span></a></li>
                <?php endif; ?>
                <?php if ($current_page < $last_page) $args['page'] = $current_page + 1; else $args['page'] = $last_page ;?>
                <li><a href="">page <?php echo $current_page; ?> sur <?php echo $last_page; ?></a></li>
                <?php if ($current_page != $args['page']): ?>
                    <li><a href="<?php echo url_for('drm', $args); ?>"> > </a></li>
                <?php endif; ?>
                    <?php $args['page'] = $last_page; ?>
                <?php if ($current_page != $args['page']): ?>
                    <li><a href="<?php echo url_for('drm', $args); ?>" class="btn_majeur page_suivante"> >> </a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?php endif; ?>
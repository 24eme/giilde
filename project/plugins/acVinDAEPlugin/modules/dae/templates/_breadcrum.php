<ol class="breadcrumb">
    <li><a href="<?php echo url_for('dae') ?>">Activités mensuelle</a></li>
    <li><a href="<?php echo url_for('dae_etablissement', array('identifiant' => $etablissement->identifiant)) ?>"><?php echo $etablissement->nom ?> (<?php echo $etablissement->identifiant ?>)</a></li>
    <li><a href="<?php echo url_for('dae_nouveau', array('identifiant' => $etablissement->identifiant)) ?>" class="active">Nouveau</a></li>
</ol>

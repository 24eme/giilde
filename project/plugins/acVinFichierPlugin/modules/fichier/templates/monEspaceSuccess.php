<ol class="breadcrumb">
    <li><a href="<?php echo url_for('fichiers') ?>">Fichiers</a></li>
    <li><a class="active" href="<?php echo url_for('fichiers_etablissement', array('identifiant' => $etablissement->identifiant)) ?>"><?php echo $etablissement->nom ?> (<?php echo $etablissement->identifiant ?>)</a></li>
</ol>

<div class="row">
    <div class="col-xs-12">
        <?php include_component('fichier', 'formEtablissementChoice', array('identifiant' => $etablissement->_id)) ?>
    </div>
    
    <div class="col-xs-12">
		<?php include_partial('fichier/history', array('etablissement' => $etablissement, 'history' => PieceAllView::getInstance()->getPiecesByEtablissement($etablissement->identifiant, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)), 'limit' => Piece::LIMIT_HISTORY)); ?>
    </div>
</div>

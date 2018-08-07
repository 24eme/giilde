<?php echo use_helper("Date"); ?>

<ol class="breadcrumb">
    <li><a href="<?php echo ($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))? url_for('fichiers') : ''; ?>">Fichiers</a></li>
    <li><a href="<?php echo ($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))? url_for('fichiers_etablissement', array('identifiant' => $etablissement->identifiant)) : ''; ?>"><?php echo $etablissement->nom ?> (<?php echo $etablissement->identifiant ?>)</a></li>
    <li class="active"><a href="">Documents</a></li>
</ol>

<div class="page-header">
    <h2>
    	Historique des documents
        <form class="form-inline pull-right col-xs-2 text-right">
		  <div class="form-group">
		    <select class="form-control select2SubmitOnChange select2 input-sm text-right" id="year" name="annee">
		    	<option value="0">Toutes années</option>
		    	<?php foreach ($years as $y): ?>
		    	<option value="<?php echo $y ?>"<?php if($y == $year): ?> selected="selected"<?php endif; ?>><?php echo $y ?></option>
		    	<?php endforeach; ?>
		    </select>
		  </div>
		</form>
    </h2>
</div>


<div class="list-group">
<?php if(count($history) > 0): ?>
	<ul class="nav nav-pills" style="margin: 0 0 20px 0;">
		<li<?php if (!$category):?> class="active"<?php endif; ?>><a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'annee' => $year))?>">Tous&nbsp;<span class="glyphicon glyphicon-file"></span>&nbsp;<?php echo count($history) - $decreases ?></a></li>
		<?php foreach ($categories as $categorie => $nbDoc): ?>
        <li<?php if ($category && $category == $categorie):?> class="active"<?php endif; ?>><a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'annee' => $year, 'categorie' => $categorie))?>"><?php echo ($categorie == 'FICHIER')? 'Document' : str_replace('cremant', ' Crémant', ucfirst(strtolower($categorie))); ?>&nbsp;<span class="glyphicon glyphicon-file"></span>&nbsp;<?php echo $nbDoc ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php
		foreach ($history as $document):
			if ($category && preg_match('/^([a-zA-Z]*)\-./', $document->id, $m)) {
				if ($m[1] != $category) { continue; }
			}
	?>
	<div class="list-group-item col-xs-12">
		<span class="col-sm-2 col-xs-12">
			<?php echo (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $document->key[PieceAllView::KEYS_DATE_DEPOT]))? format_date($document->key[PieceAllView::KEYS_DATE_DEPOT], "dd/MM/yyyy", "fr_FR") : null; ?>
		</span>
		<span class="col-sm-8 col-xs-12">
			<?php if (Piece::isVisualisationMasterUrl($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
				<?php if ($urlVisu = Piece::getUrlVisualisation($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
					<a href="<?php echo $urlVisu ?>"><?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?></a>
				<?php endif; ?>
			<?php else: ?>
				<?php if(count($document->value[PieceAllView::VALUES_FICHIERS]) > 1): ?>
				  	<a href="#" class="dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?></a>
				  	<ul class="dropdown-menu">
				  		<?php 
				  			foreach ($document->value[PieceAllView::VALUES_FICHIERS] as $file): 
				    		$infos = explode('.', $file);
				    		$extention = (isset($infos[1]))? $infos[1] : "";
				  		?>
				  		<li><a href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>?file=<?php echo $file ?>"><?php echo strtoupper($extention) ?></a></li>
				  		<?php endforeach; ?>
				  		<?php if ($urlCsv = Piece::getUrlGenerationCsvPiece($document->id, false)): ?>
				  		<li><a href="<?php echo $urlCsv ?>">CSV Généré</a></li>
				  		<?php endif; ?>
				  	</ul>
				<?php else: ?>
				<a href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>"><?php echo $document->key[PieceAllView::KEYS_LIBELLE] ?></a>
				<?php endif; ?>
			<?php endif; ?>
		</span>
		<span class="col-sm-2 col-xs-12">
		<?php if(count($document->value[PieceAllView::VALUES_FICHIERS]) > 1): ?>
		  	<a href="#" class="pull-right dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-duplicate"></span></a>
		  	<ul class="dropdown-menu">
		  		<?php 
		  			foreach ($document->value[PieceAllView::VALUES_FICHIERS] as $file): 
		    		$infos = explode('.', $file);
		    		$extention = (isset($infos[1]))? $infos[1] : "";
		  		?>
		  		<li><a href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>?file=<?php echo $file ?>"><?php echo strtoupper($extention) ?></a></li>
		  		<?php endforeach; ?>
			  	<?php if ($urlCsv = Piece::getUrlGenerationCsvPiece($document->id, false)): ?>
			  	<li><a href="<?php echo $urlCsv ?>">CSV Généré</a></li>
			  	<?php endif; ?>
		  	</ul>
		<?php else: ?>
		<a class="pull-right" href="<?php echo url_for('get_piece', array('doc_id' => $document->id, 'piece_id' => $document->value[PieceAllView::VALUES_KEY])) ?>"><span class="glyphicon glyphicon-file"></span></a>
		<?php endif; ?>		
		<?php if ($urlVisu = Piece::getUrlVisualisation($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
			<a class="pull-right" href="<?php echo $urlVisu ?>" style="margin: 0 10px;"><span class="glyphicon glyphicon-edit"></span></a>
		<?php endif; ?>	
		<?php if (Piece::isPieceEditable($document->id, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN))): ?>
			<a class="pull-right" href="<?php echo url_for('edit_fichier', array('id' => $document->id)) ?>"><span class="glyphicon glyphicon-user"></span></a>
		<?php endif; ?>
		</span>
	</div>
	<?php endforeach; ?>
<?php else: ?>
	<p class="text-center"><em>Aucun document disponible<?php if ($year): ?> pour l'année <strong><?php echo $year ?></strong><?php endif; ?></em></p>
<?php endif; ?>
</div>

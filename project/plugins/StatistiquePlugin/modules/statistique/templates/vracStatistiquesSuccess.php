<ol class="breadcrumb">
    <li><a href="<?php echo url_for('statistiques') ?>">Statistiques</a></li>
    <li><a href="<?php echo url_for('statistiques_vrac') ?>" class="active">Contrats</a></li>
</ol>

<div class="row">
    <div class="col-xs-12">
			
    		<h2><strong><?php echo $statistiquesConfig['title'] ?></strong></h2>
    		
    		<?php include_partial('formFilter', array('url' => url_for('statistiques_vrac'), 'collapseIn' => $collapseIn, 'form' => $form)) ?>
    		<hr />
    		<p><strong><?php echo number_format($nbHits, 0, ',', ' ') ?></strong> résultat<?php if ($nbHits > 1): ?>s<?php endif; ?></p>
    		
    		<?php if ($nbHits > 0): ?>
    			<?php include_partial('resultVracStatistiqueFilter', array('hits' => $hits)) ?>
    		<?php else: ?>
    			<p>Aucun résultat pour la recherche</p>
    		<?php endif; ?>
    		
    		<?php if ($nbPage > 1): ?>
    			<?php include_partial('paginationStatistiqueFilter', array('type' => 'vrac', 'nbPage' => $nbPage, 'page' => $page, 'filters' => $filters)) ?>
    		<?php endif; ?>
    		
    </div>
</div>
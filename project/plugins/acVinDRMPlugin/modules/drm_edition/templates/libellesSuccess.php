<?php use_helper("Date"); ?>
<?php use_helper('DRM'); ?>
<?php use_helper('PointsAides'); ?>
<!-- #principal -->

<?php include_partial('drm/breadcrumb', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<section id="principal" class="drm">

<?php include_partial('drm/etapes', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'etape_courante' => DRMClient::ETAPE_VALIDATION)); ?>

<form action="<?php echo url_for('drm_edition_libelles', $form->getObject()) ?>" method="post">
	<?php echo $form->renderGlobalErrors(); ?>
    <?php echo $form->renderHiddenFields(); ?>
    
    <p>Indiquer les libellés correspondant à ceux déclarés sur prodouane.</p>

	<?php if($form->hasDetailsKey(DRM::DETAILS_KEY_SUSPENDU)): ?>
	<h3 style="margin-top: 0;">Mouvements <?php echo DRMClient::$types_libelles[DRM::DETAILS_KEY_SUSPENDU] ?>s</h3>
	<table class="table table-bordered table-striped">
    	<thead>
        	<tr>
				<th class="col-xs-6">Libellé produit</th>
				<th class="col-xs-6">Libellé prodouane</th>
			</tr>
			</thead>
			<tbody>
				<?php 
					foreach ($drm->getProduitsDetails(true, DRM::DETAILS_KEY_SUSPENDU) as $hash => $detail): 
					if (isset($form[$hash])):
				?>
				<tr>
					<td><?php echo $form[$hash]->renderLabel() ?></td>
					<td<?php if ($drm->get($hash)->hasLibelleModified()): ?> class="has-warning"<?php endif; ?>><?php echo $form[$hash]->render(array('class' => 'form-control')) ?><?php echo $form[$hash]->renderError() ?></td>
				</tr>
				<?php endif; endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
	
	<?php if($form->hasDetailsKey(DRM::DETAILS_KEY_ACQUITTE)): ?>
	<h3 style="margin-top: 0;">Mouvements <?php echo DRMClient::$types_libelles[DRM::DETAILS_KEY_ACQUITTE] ?>s</h3>
	<table class="table table-bordered table-striped">
    	<thead>
        	<tr>
				<th class="col-xs-6">Libellé produit</th>
				<th class="col-xs-6">Libellé prodouane</th>
			</tr>
			</thead>
			<tbody>
				<?php 
					foreach ($drm->getProduitsDetails(true, DRM::DETAILS_KEY_ACQUITTE) as $hash => $detail): 
					if (isset($form[$hash])):
				?>
				<tr>
					<td><?php echo $form[$hash]->renderLabel() ?></td>
					<td<?php if ($drm->get($hash)->hasLibelleModified()): ?> class="has-warning"<?php endif; ?>><?php echo $form[$hash]->render(array('class' => 'form-control')) ?><?php echo $form[$hash]->renderError() ?></td>
				</tr>
				<?php endif; endforeach; ?>
		</tbody>
	</table>
    <?php endif; ?>
    
    <div class="row">
    	<div class="col-xs-6 text-left">
        	<a tabindex="-1" href="<?php echo url_for('drm_validation', $drm) ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Etape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
        	<button type="submit" class="btn btn-success">Valider</button>
        </div>
     </div>
     
</form>
</section>

<?php use_helper('Version'); ?>
<?php use_helper('Float');
$hasDontRevendique = $drm->getConfig()->getDocument()->hasDontRevendique();
 ?>
<table class="table table-striped table-condensed">
	<thead>
		<tr>
			<th class="<?php if($hasDontRevendique): ?>col-xs-1<?php else: ?>col-xs-2<?php endif; ?>">Type</th>
			<th class="col-xs-3">Produits</th>
			<th class="col-xs-1 text-right">Stock initial</th>
			<th class="col-xs-1"><?php if($hasDontRevendique): ?>(revendiqué) <?php endif; ?></th>
			<th class="col-xs-1 text-right">Entrées</th>
			<th class="col-xs-1"><?php if($hasDontRevendique): ?>&nbsp;(Rev.)<?php endif; ?></th>
			<th class="col-xs-1 text-right">Sorties</th>
			<th class="col-xs-1"><?php if($hasDontRevendique): ?>&nbsp;(Rev.)<?php else: ?>&nbsp;(Fact.)<?php endif; ?></th>
			<th class="col-xs-1 text-right"><strong>Stock final</th>
			<th class="col-xs-1"><?php if($hasDontRevendique): ?>(revendiqué)</strong>  <?php endif; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php $details = $drm->getProduitsDetails($isTeledeclarationMode, $typeDetailKey); ?>
		<?php foreach($details as $detail): ?>
      <?php $unite = ($detail->isMatierePremiere())? "hlap" : "hl"; ?>
			<tr>
				<td><?php echo $detail->getTypeDRMLibelle() ?></td>
				<td><a href="#tab=mouvements_<?php echo $detail->getTypeDRM() ?>&filtre=<?php echo strtolower($detail->getLibelle(ESC_RAW)); ?>"><?php echo $detail->getLibelle(ESC_RAW) ?></a></td>

        <td class="text-right <?php echo isVersionnerCssClass($detail, 'total_debut_mois') ?>">
          <strong><?php echoFloat($detail->total_debut_mois) ?></strong>&nbsp;<span class="unite"><?php echo $unite; ?></span>&nbsp;
        </td>

        <td class="<?php echo isVersionnerCssClass($detail, 'total_debut_mois') ?>"><?php if($detail->stocks_debut->exist('dont_revendique')): ?>(<?php echoFloat($detail->stocks_debut->dont_revendique) ?>&nbsp;<span class="unite"><?php echo $unite; ?></span>)<?php endif; ?></td>

      	<td class="text-right <?php echo isVersionnerCssClass($detail, 'total_entrees') ?>"><?php echoFloat($detail->total_entrees) ?>&nbsp;<span class="unite"><?php echo $unite; ?></span></td>
        <td class="<?php echo isVersionnerCssClass($detail, 'total_entrees_revendique') ?>">
          <?php if(!$isTeledeclarationMode && $hasDontRevendique): ?>&nbsp;(<?php echoFloat($detail->total_entrees_revendique) ?>&nbsp;<span class="unite"><?php echo $unite; ?></span>)<?php endif; ?>
        </td>
				<td class="text-right <?php echo isVersionnerCssClass($detail, 'total_sorties') ?>"><?php echoFloat($detail->total_sorties) ?>&nbsp;<span class="unite"><?php echo $unite; ?></span></td>
        <td>
          <?php if(!$isTeledeclarationMode && $hasDontRevendique): ?>&nbsp;(<?php echoFloat($detail->total_sorties_revendique) ?>&nbsp;<span class="unite"><?php echo $unite; ?></span>)<?php endif; ?>
          <?php if(!$isTeledeclarationMode && !$hasDontRevendique): ?>&nbsp;(<?php echoFloat($detail->total_facturable) ?>&nbsp;<span class="unite"><?php echo $unite; ?></span>)<?php endif; ?>
        </td>
				<td class="text-right <?php echo isVersionnerCssClass($detail, 'total') ?>"><strong><?php echoFloat($detail->total) ?></strong>&nbsp;<span class="unite"><?php echo $unite; ?></span>&nbsp;</td>
        <td class="<?php echo isVersionnerCssClass($detail, 'total') ?>"><?php if($detail->stocks_fin->exist('dont_revendique')): ?>(<?php echoFloat($detail->stocks_fin->dont_revendique) ?>&nbsp;<span class="unite"><?php echo $unite; ?></span>)<?php endif; ?></td>

			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

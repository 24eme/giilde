<table  class="table table-striped table-filter table-bordered" style="border-top:none;">
	<thead>
		<tr>
			<th colspan="2" style="background:none;border-color:#fff;">&nbsp;</th>
			<th colspan="2" class="text-center"><?php echo $dsnegoce->millesime ?></th>
			<th colspan="2" class="text-center"><?php echo $dsnegoce->millesime - 1 ?>, précédent et non millésimé</th>
		</tr>
  		<tr>
  			<th>Produit</th>
    		<th class="text-center"><?php echo ucfirst(format_date($dsnegoce->date_stock, 'MMMM yyyy', 'fr_FR')) ?></th>
  			<th class="text-center">Stock</th>
  			<th class="text-center">Disponible</th>
  			<th class="text-center">Stock</th>
  			<th class="text-center">Disponible</th>
  		</tr>
	</thead>
	<tbody>
    <?php
      foreach($dsnegoce->declaration as $hash => $produit):
        $libelle = $produit->libelle;
        foreach($produit->detail as $detail => $stocks):
          if ($stocks->denomination_complementaire) {
            $libelle .= ' '.$stocks->denomination_complementaire;
          }
    ?>
		<tr>
			<td><strong><?php echo $libelle ?></strong></td>
			<td class="text-right"><?php if($stocks->stock_initial_millesime_courant): ?><?php echoFloat($stocks->stock_initial_millesime_courant) ?>&nbsp;<span class="text-muted">hl</small><?php endif; ?></td>
			<td class="text-right"><?php if($stocks->stock_declare_millesime_courant): ?><?php echoFloat($stocks->stock_declare_millesime_courant) ?>&nbsp;<span class="text-muted">hl</small><?php endif; ?></td>
			<td class="text-right"><?php if($stocks->dont_vraclibre_millesime_courant): ?><?php echoFloat($stocks->dont_vraclibre_millesime_courant) ?>&nbsp;<span class="text-muted">hl</small><?php endif; ?></td>
			<td class="text-right"><?php if($stocks->stock_declare_millesime_anterieur): ?><?php echoFloat($stocks->stock_declare_millesime_anterieur) ?>&nbsp;<span class="text-muted">hl</small><?php endif; ?></td>
			<td class="text-right"><?php if($stocks->dont_vraclibre_millesime_anterieur): ?><?php echoFloat($stocks->dont_vraclibre_millesime_anterieur) ?>&nbsp;<span class="text-muted">hl</small><?php endif; ?></td>
		</tr>
		<?php endforeach;endforeach; ?>
	</tbody>
</table>

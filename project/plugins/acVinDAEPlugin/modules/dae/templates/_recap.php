<table  class="table table-striped table-filter">
	<thead>
		<tr>
			<th>Produit</th>
			<th>Millésime</th>
			<th>Destination</th>
			<th>Clients</th>
			<th>Condi.</th>
			<th>Qté</th>
			<th>Prix HT</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($daes as $id => $dae): ?>
		<tr>
			<td><strong><?php echo $dae->produit_libelle ?></strong><?php if ($dae->primeur): ?> <span class="text-muted">Primeur</span><?php endif; ?><br /><?php if ($dae->label_libelle): ?><span class="text-muted"><?php echo $dae->label_libelle ?></span><?php endif; ?><?php if ($dae->label_libelle && $dae->mention_libelle): ?> - <?php endif;?><?php if ($dae->mention_libelle): ?><span class="text-muted"><?php echo $dae->mention_libelle ?></span><?php endif; ?></td>
			<td><?php echo $dae->millesime ?></td>
			<td><?php echo $dae->destination_libelle ?></td>
			<td><?php echo $dae->type_acheteur_libelle ?></td>
			<td><?php echo $dae->contenance_libelle ?></td>
			<td class="text-right"><?php echo $dae->quantite ?>&nbsp;<span class="text-muted"><?php if ($dae->conditionnement_key == 'BOUTEILLE'): ?>btl<?php elseif ($dae->conditionnement_key == 'BIB'): ?>bib<?php else: ?>hl<?php endif; ?></span></td>
			<td class="text-right"><?php echo $dae->prix_unitaire ?>&nbsp;<span class="text-muted">€&nbsp;<?php if ($dae->conditionnement_key == 'BOUTEILLE'): ?>btl<?php elseif ($dae->conditionnement_key == 'BIB'): ?>bib<?php else: ?>hl<?php endif; ?></span></td>
			<td><a href="<?php echo url_for('dae_nouveau', array('identifiant' => $dae->identifiant, 'id' => $dae->_id, 'periode' => $dae->date)) ?>"><span class="glyphicon glyphicon-edit"></span></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
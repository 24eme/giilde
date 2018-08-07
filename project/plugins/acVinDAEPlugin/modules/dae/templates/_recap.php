<table  class="table table-striped" style="margin-top: 20px;">
	<thead>
		<tr>
			<th>Produit</th>
			<th>Millésime</th>
			<th>Destination</th>
			<th>Acheteur</th>
			<th>Condi.</th>
			<th>Volume</th>
			<th>Prix HT</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($daes as $id => $dae): ?>
		<tr>
			<td><strong><?php echo $dae->produit_libelle ?></strong><?php if ($dae->label_libelle): ?><br /><span class="text-muted"><?php echo $dae->label_libelle ?></span><?php else: ?><br />&nbsp;<?php endif; ?></td>
			<td><?php echo $dae->millesime ?></td>
			<td><?php echo $dae->destination_libelle ?></td>
			<td><?php echo $dae->type_acheteur_libelle ?></td>
			<td><?php echo $dae->conditionnement_libelle ?></td>
			<td class="text-right"><?php echo $dae->volume_hl ?>&nbsp;<span class="text-muted">hl</span></td>
			<td class="text-right"><?php echo $dae->prix_hl ?>&nbsp;<span class="text-muted">€/hl</span></td>
			<td><a href="<?php echo url_for('dae_nouveau', array('identifiant' => $dae->identifiant, 'id' => $dae->_id, 'periode' => $dae->date)) ?>"><span class="glyphicon glyphicon-edit"></span></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
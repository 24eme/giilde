<?php
use_helper('Date');
?>
<h2>Historique des factures</h2>
<?php if (count($factures)): ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th class="col-xs-1">Type</th>
            <th class="col-xs-1">Document</th>
            <th class="col-xs-1">Numéro</th>
            <th class="col-xs-4">Date de facturation</th>
            <th class="col-xs-1 text-right">Montant&nbsp;HT</th>
            <th class="col-xs-1 text-right">Montant&nbsp;TTC</th>
            <th class="col-xs-3"></th>
        </tr>
    </thead>
    <tbody>
        <?php $fc = FactureClient::getInstance(); ?>
        <?php foreach ($factures->getRawValue() as $facture): ?>
            <?php $f = $fc->find($facture->id); ?>
            <?php $date = $date = format_date($facture->value[FactureSocieteView::VALUE_DATE_FACTURATION], 'dd/MM/yyyy') . ' (créée le ' . $fc->getDateCreation($facture->id) . ')'; ?>
            <tr>
                <td><?php if ($f->isAvoir()): ?>AVOIR<?php else: ?>FACTURE<?php endif; ?></td>
                <td><?php if ($f->isFactureDRM()): ?>DRM<?php elseif($f->isFactureSV12()): ?>SV12<?php elseif($f->isFactureDivers()): ?>Libre<?php endif; ?></td>
                <td>N°&nbsp;<?php echo $f->numero_piece_comptable ?></td>
                <td><?php echo $date; ?> <?php if($f->isRedressee()): ?><span class="label label-warning">Redressée</span><?php endif;?></td>
                <td class="text-right"><?php echo echoFloat($f->total_ht); ?>&nbsp;€</td>
                <td class="text-right"><?php echo echoFloat($f->total_ttc); ?>&nbsp;€</td>
                <td class="text-right"><div class="btn-group text-left">
                    <?php if ($f->isRedressable()): ?>
                        <a onclick="return confirm('Êtes-vous sur de vouloir annuler cette facture en créant un avoir ?');" href="<?php echo url_for("facture_defacturer", array("id" => $f->_id)) ?>" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-repeat"></span>&nbsp;Défacturer</a>
                    <?php endif; ?>
                    <a href="<?php echo url_for("facture_pdf", array("id" => $f->_id)) ?>" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-file"></span>&nbsp;Visualiser</a>
                </div></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <p class="text-center text-muted"><i>Aucune Facture</i></p>
<?php endif; ?>

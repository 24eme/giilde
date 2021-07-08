<?php
use_helper('Float');
?>
<?php include_partial('facture/preTemplate'); ?>
<ol class="breadcrumb">
    <li class="visited"><a href="<?php echo url_for('facture') ?>">Factures</a></li>
    <li class="active"><a href="<?php echo url_for('facture_mouvements') ?>">Facturation libre</a></li>
</ol>

<div class="row row-margin">
    <div class="col-xs-8">
        <h2>Liste des facturations libres</h2>

    </div>

    <div class="col-xs-4" style="padding-top: 20px;">
        <a href="<?php echo url_for("facture_mouvements_nouveaux"); ?>" class="btn btn-warning pull-right ">Créer une nouvelle facturation libre</a>

    </div>
</div>
<br/>
<div class="row row-margin">
    <div class="col-xs-12">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="col-xs-4">Intitulé</th>
                    <th class="col-xs-1 text-center" >Date</th>
                    <th class="col-xs-2 text-center" >Nb mouvements (à facturer)</th>
                    <th class="col-xs-2 text-right">Montant (Restant à facturer)</th>
                    <th class="col-xs-2">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($factureMouvementsAll as $factureMouvement): ?>
                    <tr class="vertical-center">
                        <td class="col-xs-4 text-left"><?php echo $factureMouvement->libelle; ?></td>
                        <td class="col-xs-1 text-center"><?php echo Date::francizeDate($factureMouvement->date); ?></td>
                        <td class="col-xs-2 text-center"><?php echo $factureMouvement->getNbMvts() . ' (' . $factureMouvement->getNbMvtsAFacture() . ')'; ?></td>
                        <td class="col-xs-2 text-right"><?php echo sprintFloat($factureMouvement->getTotalHt()) . '&nbsp;&euro; (' . sprintFloat($factureMouvement->getTotalHtAFacture()) . '&nbsp;&euro;)'; ?></td>
                        <td class="col-xs-2 text-center">

                            <div class="col-xs-6 text-right">
                                <a href="<?php echo url_for('facture_mouvements_edition', array('id' => $factureMouvement->identifiant)); ?>" class="btn btn-default">Modifier</a>
                            </div>
                            <?php if (!$factureMouvement->getNbMvtsAFacture()): ?>
                                <div class="col-xs-6 text-left">
                                    <a class="btn btn-default" href="<?php echo url_for('facture_mouvements_supprimer', array('id' => $factureMouvement->identifiant)); ?>">
                                        <span class="glyphicon glyphicon-remove"></span>
                                    </a>
                                </div>
                            <?php endif; ?>


                        </td>
                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<br/>
<div class="row">
    <div class="col-xs-12">
        <a href="<?php echo url_for("facture"); ?>" class="btn btn-default">Retour à la facturation</a>

    </div>
</div>
<?php include_partial('facture/postTemplate'); ?>

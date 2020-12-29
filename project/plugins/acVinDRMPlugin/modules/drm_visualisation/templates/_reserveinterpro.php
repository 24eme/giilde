<?php use_helper('DRM'); ?>
<?php
$produits = $drm->getProduitsReserveInterpro();
if (count($produits)): ?>
    <div class="row">
        <div class="col-xs-12">
            <h3>Réserve interprofessionnelle</small></h3>
            <p>Le 3 octobre 2020, l'assemblée générale d'IVBD a voté la mise en place d'une réserve interprofessionnelle activée pour les rendements au-delà de 23&nbsp;hl/ha. Le tableau suivant récapitule le volume de votre réserve :
            <div class="col-xs-8" style="padding-left: 0px;">
             <table class="table table-bordered table-striped table-condensed">
                <thead>
                    <tr>
                        <th class="col-xs-8"><strong>Produit</strong></th>
                        <th class="col-xs-4 text-right"><strong>Volumes en réserve</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $p) : ?>
                                <tr class="text-right">
                                    <td style="text-align: left;"><strong><?php echo $p->getLibelle(); ?></strong></td>
                                    <td><?php echoFloat($p->getRerserveIntepro()); ?>&nbsp;hl</td>
                                </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
          </div>
        </div>
    </div>
<?php endif; ?>

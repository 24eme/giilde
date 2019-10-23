<?php
use_helper('Float');
use_helper('Date');
use_helper('Mouvement');

if (!isset($isTeledeclarationMode)) {
    $isTeledeclarationMode = false;
}
$hasDontRevendique = ConfigurationClient::getCurrent()->hasDontRevendique();
?>
<p style="margin-top: 10px;"><?php echo getPointAideText('drm','visualisation_cvo_montant'); ?></p>
<?php if (count($mouvementsByProduit) > 0): ?>
    <?php if (isset($hamza_style)) : ?>
        <div class="row" style="margin-top: 10px; margin-bottom: 10px;">
            <div class="col-xs-12">
                <div class="form-group">
                    <input type="hidden" data-placeholder="Saisissez un produit, un type de mouvement, un numéro de contrat, un pays d'export, etc. :" data-hamzastyle-container="#table_mouvements" class="hamzastyle" style="width: 970px"/>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <table id="table_mouvements" class="table table-striped table-condensed">
        <thead>
            <tr>
                <th class="col-xs-2">Type</th>
                <th class="col-xs-4">Produits</th>
                <th class="<?php if($hasDontRevendique): ?>col-xs-3<?php else: ?>col-xs-4<?php endif; ?>">Mouvement</th>
                <th class="<?php if($hasDontRevendique): ?>col-xs-1<?php else: ?>col-xs-2<?php endif; ?>"><span class="pull-right">Volume</span></th>
                <?php if($hasDontRevendique): ?>
                  <th class="col-xs-2"><span class="pull-left">(dont revendiqué)</span></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; ?>
            <?php foreach ($mouvementsByProduit[$typeKey] as $produit_hash => $mouvements):
                $produitDetail = $drm->getDetailsByHash($produit_hash);
                $produit_libelle = $produitDetail->getLibelle();
                $produit_libelle_word = $produitDetail->getLibelle(ESC_RAW);
                $libelleDoc = DRMClient::getInstance()->getLibelleFromId($drm->_id);
                ?>
                <tr data-words='<?php echo json_encode(array_merge(array(strtolower($produit_libelle_word), strtolower("Stock début"))), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>' id="<?php echo mouvement_get_id($drm->_id) ?>" class="hamzastyle-item">
                    <td><?php echo $produitDetail->getTypeDRMLibelle() ?></td>
                    <td><?php if($drm->version): ?><small class="text-muted"><?php echo $drm->version ?></small> <?php endif; ?><a href="#tab=mouvements_<?php echo $typeKey ?>&filtre=<?php echo strtolower($produit_libelle); ?>"><strong><?php echo $produit_libelle ?></strong></a></td>
                    <td><strong>Stock début</strong></td>
                    <td class="text-right"><strong><?php echoFloat($produitDetail->total_debut_mois) . ' hl'; ?></strong></td>
                    <?php if($hasDontRevendique && $produitDetail->stocks_debut->exist('dont_revendique')): ?>
                    <td class="text-left"><strong>(<?php echo ($produitDetail->stocks_debut->dont_revendique) ? sprintFloat($produitDetail->stocks_debut->dont_revendique) : "0.00"; ?>)</strong></td>
                    <?php endif; ?>
                </tr>
                <?php foreach ($mouvements as $mouvement): ?>
                    <tr data-words='<?php echo json_encode(array_merge(array(strtolower($mouvement->getRawValue()->produit_libelle), strtolower($mouvement->type_libelle), strtolower($mouvement->detail_libelle))), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>' id="<?php echo mouvement_get_id($mouvement) ?>" class="hamzastyle-item <?php echo ($mouvement->facturable && (!$isTeledeclarationMode || $visualisation)) ? " facturable" : ""; ?>">
                        <td><?php echo $mouvement->type_drm_libelle ?></td>
                        <td><a href="#tab=mouvements_<?php echo $typeKey ?>&filtre=<?php echo strtolower($produit_libelle); ?>"><?php if($drm->version): ?><small class="text-muted"><?php echo ($mouvement->version) ? $mouvement->version : "M00" ?></small> <?php endif; ?><?php echo $mouvement->produit_libelle ?></a></td>
                        <td><?php
                            if ($mouvement->vrac_numero && $mouvement->vrac_numero!="SUPPRIME") {
                                echo (!isset($no_link) || !$no_link) ? '<a href="' . url_for("vrac_visualisation", array("numero_contrat" => $mouvement->vrac_numero)) . '">' : '';
                                if(preg_match("/^(creationvractirebouche_details|creationvrac_details)/",$mouvement->type_hash)){
                                  echo $mouvement->type_libelle . ' ' . $mouvement->detail_libelle;
                                }else{
                                  echo $mouvement->type_libelle . ' ' . $mouvement->numero_archive;
                                }
                                echo (!isset($no_link) || !$no_link) ? '</a>' : '';
                            } else {
                                echo $mouvement->type_libelle . ' ' . $mouvement->detail_libelle;
                                if($mouvement->vrac_numero == "SUPPRIME"){
                                  echo " supprimé";
                                }
                            }
                            ?>
                        </td>
                        <td <?php echo ($mouvement->volume > 0) ? ' class="positif"' : 'class="negatif"'; ?> >
                            <span class="pull-right"><?php echoSignedFloat($mouvement->volume); ?></span>
                        </td>
                      <?php if($hasDontRevendique): ?>  <td></td> <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <tr data-words='<?php echo json_encode(array_merge(array(strtolower($produit_libelle_word), strtolower("Stock fin"))), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>' id="<?php echo mouvement_get_id($drm->_id) ?>" class="hamzastyle-item">
                    <td><?php echo $produitDetail->getTypeDRMLibelle() ?></td>
                    <td><?php if($drm->version): ?><small class="text-muted"><?php echo $drm->version ?></small> <?php endif; ?><a href="#tab=mouvements&filtre=<?php echo strtolower($produit_libelle); ?>"><strong><?php echo $produit_libelle ?></strong></a></td>
                    <td><strong>Stock fin</strong></td>
                    <td class="text-right"><strong><?php echoFloat($produitDetail->total) . ' hl'; ?></strong></td>
                    <?php if($hasDontRevendique && $produitDetail->stocks_fin->exist('dont_revendique')): ?>
                    <td class="text-left"><strong>(<?php echo ($produitDetail->stocks_fin->dont_revendique) ? sprintFloat($produitDetail->stocks_fin->dont_revendique) : "0.00"; ?>)</strong></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-center"><em>Aucun mouvement</em></p>
<?php endif; ?>

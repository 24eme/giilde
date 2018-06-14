<?php
/* Fichier : _marcheRecapitulatif.php
 * Description : Fichier php correspondant à la vue partielle de /vrac/XXXXXXXXXXX/recapitulatif
 * Affichage du recapitulatif de la partie marche du contrat
 * Auteur : Petit Mathurin - mpetit[at]actualys.com
 * Version : 1.0.0
 * Derniere date de modification : 28-05-12
 */
use_helper('Vrac');
use_helper('Float');
$cpt = 0;
?>
<div class="bloc_form bloc_form_condensed" >
    <?php if (!$isTeledeclarationMode): ?>
        <div id="marche_recapitulatif_original" class="<?php echoClassLignesVisu($cpt); ?>">
            <label>En attente de l'original :</label>
            <span><?php echo ($vrac->attente_original) ? 'Oui' : 'Non'; ?></span>
        </div>
    <?php endif; ?>
    <div id="marche_recapitulatif_typeTransaction" class="<?php echoClassLignesVisu($cpt); ?>" >
        <label>Type de transaction :</label>
        <span><?php echo showType($vrac); ?></span>
    </div>
    <?php if ($isTeledeclarationMode): ?>
        <div id="marche_recapitulatif_produit" class="<?php echoClassLignesVisu($cpt); ?>" >
            <label>Produit :</label>
            <span><?php echo $vrac->produit_libelle . '&nbsp;-&nbsp;' . $vrac->millesime; ?></span>
        </div>

    <?php else: ?>
        <div id="marche_recapitulatif_produit" class="<?php echoClassLignesVisu($cpt); ?>" >
            <label>Produit :</label>
            <span><?php echo $vrac->produit_libelle; ?></span>
        </div>

        <div id="marche_recapitulatif_millesime" class="<?php echoClassLignesVisu($cpt); ?>" >
            <label>
                <?php echo $vrac->getMillesimeLabel() . ' :'; ?>
            </label>
            <span>
                <?php echo ($vrac->millesime)? $vrac->millesime : 'Non millésimé'; ?>
            </span>
        </div>
    <?php endif; ?>
    <?php if ($isTeledeclarationMode): ?>
        <?php if ($vrac->categorie_vin == VracClient::CATEGORIE_VIN_DOMAINE) : ?>
            <div id="marche_recapitulatif_domaine" class="<?php echoClassLignesVisu($cpt); ?>">
                <label>
                    Domaine :
                </label>
                <span>
                    <?php echo $vrac->domaine; ?>
                </span>
            </div>
        <?php else: ?>
            <div id="marche_recapitulatif_type" class="<?php echoClassLignesVisu($cpt); ?> ">
                <label>
                    Type :
                </label>
                <span>
                    <?php echo $vrac->categorie_vin; ?>
                </span>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div id="marche_recapitulatif_type" class="<?php echoClassLignesVisu($cpt); ?> ">
            <label>
                Type :
            </label>
            <span>
                <?php echo $vrac->categorie_vin; ?>
            </span>
        </div>

        <?php if ($vrac->categorie_vin == VracClient::CATEGORIE_VIN_DOMAINE) : ?>
            <div id="marche_recapitulatif_domaine" class="<?php echoClassLignesVisu($cpt); ?>">
                <label>
                    Domaine :
                </label>
                <span>
                    <?php echo $vrac->domaine; ?>
                </span>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <div id="marche_recapitulatif_volumePropose" class="<?php echoClassLignesVisu($cpt); ?>">
        <label>
            Volumes proposés:
        </label>
        <span>
            <?php
            echo showRecapVolumePropose($vrac);
            ?>
            <?php if (!$isTeledeclarationMode && !$vrac->isVise() && $vrac->isVin()): ?>
                (stock commercialisable <?php echoFloat($vrac->getStockCommercialisable()) ?> hl)
            <?php endif; ?>
        </span>
    </div>

    <?php if (!$isTeledeclarationMode): ?>
        <div id="marche_recapitulatif_volumeEnleve" class="<?php echoClassLignesVisu($cpt); ?>">
            <label>
                Volumes enlevé:
            </label>
            <span>
                <?php
                echo (is_null($vrac->volume_enleve)) ? '0.00 hl' : (sprintf('%01.02f',$vrac->volume_enleve) . ' hl');
                ?>
            </span>
        </div>
    <?php endif; ?>
    <div id="marche_recapitulatif_prixUnitaire" class="<?php echoClassLignesVisu($cpt); ?>">
        <label>
            Prix unitaire:
        </label>
        <span>
            <?php echo showRecapPrixUnitaire($vrac); ?>
        </span>
    </div>
    <?php if (!$isTeledeclarationMode): ?>
        <div id="marche_recapitulatif_prixTotal" class="<?php echoClassLignesVisu($cpt); ?>">
            <label>
                Prix :
            </label>
            <span><?php echo showRecapPrixTotal($vrac); ?></span>
        </div>
    <?php endif; ?>
</div>

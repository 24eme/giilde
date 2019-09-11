<?php
use_helper('Float');
use_helper('Vrac');
use_helper('Date');
use_helper('PointsAides');
?>

<?php if(count($vracs->rows) > 0): ?>
<?php if(isset($hamza_style) && $hamza_style) : ?>
    <h3>Filtrer</h3>
    <div class="form-group">
        <input type="hidden" data-placeholder="Saisissez un numéro de contrat, un soussigné ou un produit" data-hamzastyle-container="#table_contrats" class="hamzastyle form-control" />
    </div>
<?php endif; ?>


<table id="table_contrats" class="table">
    <thead>
        <tr>
        <th>Contrat<?php echo getPointAideHtml('vrac','dernier_contrat_nature'); ?></th>
            <th style="width: 110px;">Date<?php echo getPointAideHtml('vrac','dernier_contrat_date'); ?></th>
            <th>Soussignés<?php echo getPointAideHtml('vrac','dernier_contrat_soussignes'); ?></th>
            <th>Produit (Millésime)<?php echo getPointAideHtml('vrac','dernier_contrat_produits'); ?></th>
            <th style="width: 90px;">Vol.&nbsp;prop. / Vol.&nbsp;enl.<?php echo getPointAideHtml('vrac','dernier_contrat_volume'); ?></th>
            <th style="width: 50px;">Prix<?php echo getPointAideHtml('vrac','dernier_contrat_prix'); ?></th>
            <th style="width: 90px;">Visu.<?php echo getPointAideHtml('vrac','dernier_contrat_acces_visu'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $contrats = array();
        foreach ($vracs->rows as $contrat) {
          $contrats[] = $contrat;
        }
        foreach ($contrats as $value) {
            // $elt = $value->getRawValue()->value;
                $v = VracClient::getInstance()->find($value->id, acCouchdbClient::HYDRATE_JSON);
                ?>
                <tr data-words='<?php echo json_encode(array_merge(array(strtolower($v->acheteur->nom),
                                                                         strtolower($v->vendeur->nom),
                                                                         strtolower($v->mandataire->nom),
                                                                         strtolower($v->produit_libelle),
                                                                         strtolower($v->numero_archive),
                                                                         strtolower($v->millesime),
                                                                         strtolower(VracClient::$types_transaction[$v->type_transaction]))
                                                       ), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>' id="<?php echo vrac_get_id($value) ?>"
                    class="<?php echo statusCssClass($v->valide->statut) ?> hamzastyle-item vertical-center">

                    <td class="text-center">
                        <a name="ligne_<?php echo vrac_get_id($value) ?>"></a>
                        <span class="<?php echo typeToPictoCssClass($v->type_transaction) ?> pointer" style="font-size: 24px; " data-toggle="tooltip" title="<?php echo tooltipForPicto($v->type_transaction) ?>"></span>
                        <?php if($v->valide->statut): ?>
                        <a href="<?php echo url_for('vrac_visualisation', array('numero_contrat' => $v->numero_contrat)) ?>">
                        <?php else: ?>
                        <a href="<?php echo url_for('vrac_redirect_saisie', array('numero_contrat' => $v->numero_contrat)) ?>">
                        <?php endif; ?>
                        <?php if($v->numero_archive) : if (preg_match('/^DRM/', $v->numero_contrat)) { echo tooltipForPicto($v->type_transaction); } else { echo $v->numero_archive ; } elseif(!$v->valide->statut || $v->valide->statut == VracClient::STATUS_CONTRAT_BROUILLON): ?>Brouillon<?php else: ?>Non visé<?php endif; ?>

                        </a>
                        <br />
                        <?php if($v && isset($v->teledeclare) && $v->teledeclare): ?>
                        Télédeclaré
                        <?php endif; ?>
                        <?php if (preg_match('/^DRM/', $v->numero_contrat)) : ?>
                        <span class="text-muted" style="font-size: 12px;">issu de DRM</span>
                        <?php else: ?>
                        <span class="text-muted" style="font-size: 12px;"><?php echo formatNumeroBordereau($v->numero_contrat) ?></span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if($v->valide->statut && $v->date_signature): ?>
                               <span class="text-muted"><span class="glyphicon glyphicon-pencil" aria-hidden="true" title="Date de signature"></span> <?php echo format_date($v->date_signature, "dd/MM/yyyy", "fr_FR"); ?></span>
                        <?php endif; ?>
                        <?php if($v->valide->statut && $v->date_visa): ?>
                            <span class="glyphicon glyphicon-check" aria-hidden="true" title="Date de visa"></span> <?php echo format_date($v->date_visa, "dd/MM/yyyy", "fr_FR"); ?><br/>
                        <?php endif; ?>
                    </td>

                    <td>
        <?php
          if ($v->vendeur_identifiant):
            echo '<div class="vrac_nom">';
            if($v->teledeclare) {
              echo ($v->valide->date_signature_vendeur)?
              '<span class="glyphicon glyphicon-check" ></span>&nbsp;' : '<span class="glyphicon glyphicon-pencil" ></span>&nbsp;';
            }else{
              echo '<span class="glyphicon glyphicon-minus"></span>&nbsp;';
            }
            echo (!isset($teledeclaration) || !$teledeclaration) ?
                  'Vendeur : ' . link_to($v->vendeur->nom, 'vrac/recherche?identifiant=' . preg_replace('/ETABLISSEMENT-/', '', $v->vendeur_identifiant)) : 'Vendeur : ' . $v->vendeur->nom;
            echo "</div>";
           endif;
        ?>
        <br />
        <?php
        if ($v->acheteur_identifiant):
          echo '<div class="vrac_nom">';
          if($v->teledeclare) {
            echo ($v->valide->date_signature_acheteur)?
            '<span class="glyphicon glyphicon-check" ></span>&nbsp;' : '<span class="glyphicon glyphicon-pencil" ></span>&nbsp;';
          }else{
            echo '<span class="glyphicon glyphicon-minus"></span>&nbsp;';
          }
          echo (!isset($teledeclaration) || !$teledeclaration) ?
                'Acheteur : ' . link_to($v->acheteur->nom, 'vrac/recherche?identifiant=' . preg_replace('/ETABLISSEMENT-/', '', $v->acheteur_identifiant)) : 'Acheteur : ' . $v->acheteur->nom;
          echo "</div>";
         endif; ?>
        <?php
            $has_representant = ($v->representant_identifiant != $v->vendeur_identifiant) ? $v->representant_identifiant : 0;
            if ($has_representant) {
              echo '<br/>';
              if($v->vendeur_identifiant) {
                if($v->teledeclare) {
                  echo ($v->valide->date_signature_vendeur)?
                        '<span class="glyphicon glyphicon-check" ></span>&nbsp;' : '<span class="glyphicon glyphicon-pencil" ></span>&nbsp;';
                } else {
                  echo '<span class="glyphicon glyphicon-minus"></span>&nbsp;';
                }
                echo (!isset($teledeclaration) || !$teledeclaration) ?
                'Representant : ' . link_to($v->representant->nom, 'vrac/recherche?identifiant=' . preg_replace('/ETABLISSEMENT-/', '', $v->representant_identifiant)) : 'Representant : ' . $v->representant_identifiant;
              }
            }
            ?>
        <?php if($v->mandataire_identifiant): ?>
            <br />
            <div class="vrac_nom">
        <?php
              if($v->teledeclare) {
                echo ($v->valide->date_signature_courtier)?
                '<span class="glyphicon glyphicon-check" ></span>&nbsp;' : '<span class="glyphicon glyphicon-pencil" ></span>&nbsp;';
              }else{
                echo '<span class="glyphicon glyphicon-minus"></span>&nbsp;';
              }
              echo (!isset($teledeclaration) || !$teledeclaration) ?
                    'Courtier : ' . link_to($v->mandataire->nom, 'vrac/recherche?identifiant=' . preg_replace('/ETABLISSEMENT-/', '', $v->mandataire_identifiant)) : 'Courtier : ' . $v->mandataire->nom;
              echo "</div>";
             endif; ?>
                    </td>

                    <td><?php

            $produit = ($v->type_transaction == VracClient::TYPE_TRANSACTION_VIN_VRAC || $v->type_transaction == VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE)? $v->produit_libelle : $v->cepage_libelle;
            $millesime = $v->millesime ? $v->millesime : 'nm';
            if ($produit)
                echo "<b>$produit</b> ($millesime)";?></td>
                     <td class="text-right">
        <?php
        if (isset($v->volume_propose)) {
            echoFloat($v->volume_propose);
            echo '&nbsp;'.VracConfiguration::getInstance()->getUnites()[$v->type_transaction]['volume_initial']['libelle'].'<br/>';
            if ($v->volume_enleve) {
            echo '<span class="text-muted">';
                echoFloat($v->volume_enleve);
                echo '&nbsp;'.VracConfiguration::getInstance()->getUnites()[$v->type_transaction]['volume_vigueur']['libelle'];
            echo '</span>';
          }
        }
        ?>
                    </td>
                    <td class="text-right">
                    <?php if (isset($v->prix_initial_unitaire_hl) && $v->prix_initial_unitaire_hl):
                            echoFloat($v->prix_initial_unitaire_hl);
                            echo "&nbsp;".VracConfiguration::getInstance()->getUnites()[$v->type_transaction]['prix_initial_unitaire']['libelle'] ;
                          elseif($v->valide->statut != VracClient::STATUS_CONTRAT_BROUILLON && $v->valide->statut):
                    ?>
                    <a href="<?php echo url_for('vrac_marche', array('numero_contrat' => $v->numero_contrat, 'urlretour' => $sf_request->getUri()."#ligne_".vrac_get_id($value))) ?>" class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-pencil"></span>&nbsp;Éditer</a>
                  <?php endif;?>
                    </td>
                    <?php if(isset($teledeclaration) && $teledeclaration):
                      $statut = $v->valide->statut;
                      $toBeSigned = VracClient::getInstance()->toBeSignedBySociete($statut, $societe, $v->valide->date_signature_vendeur, $v->valide->date_signature_acheteur, $v->valide->date_signature_courtier);
                       ?>
                      <td class="text-center">

                      <?php if (($statut == VracClient::STATUS_CONTRAT_NONSOLDE) || ($statut == VracClient::STATUS_CONTRAT_SOLDE)): ?>
                          <a class="btn btn-default" href="<?php echo url_for('vrac_visualisation', array('numero_contrat' => $v->numero_contrat)) ?>">
                              <span class="glyphicon glyphicon-eye-open"></span>&nbsp;Visualiser
                          </a>
                       <?php  elseif ($statut == VracClient::STATUS_CONTRAT_ATTENTE_SIGNATURE): ?>
                          <a class="btn btn-default" href="<?php echo url_for('vrac_visualisation', array('numero_contrat' => $v->numero_contrat)) ?>">
                             <?php  if ($toBeSigned) : ?>
                              <span class="glyphicon glyphicon-pencil"></span>&nbsp;Signer
                              <?php  else : ?>
                              <span class="glyphicon glyphicon-eye-open"></span>&nbsp;Visualiser
                              <?php  endif; ?>
                          </a>
                      <?php elseif ($statut == VracClient::STATUS_CONTRAT_BROUILLON && ($societe->identifiant == substr($v->createur_identifiant, 0,6))): ?>
                           <a class="btn btn-warning" href="<?php echo url_for('vrac_redirect_saisie', array('numero_contrat' => $v->numero_contrat)) ?>">
                               <span class="glyphicon glyphicon-pencil"></span>&nbsp;Continuer
                          </a>
                      <?php endif;  ?>
                    </td>
                    <?php else: ?>

                      <td class="text-center">
                          <?php if($v->valide->statut != VracClient::STATUS_CONTRAT_BROUILLON && $v->valide->statut): ?>
                              <a class="btn btn-sm btn-default" href="<?php echo url_for('vrac_visualisation', array('numero_contrat' => $v->numero_contrat)) ?>">Visualiser</a>
                          <?php else: ?>
                              <a class="btn btn-sm btn-default" href="<?php echo url_for('vrac_redirect_saisie', array('numero_contrat' => $v->numero_contrat)) ?>">Continuer</a>
                          <?php endif; ?>
                      </td>

                    <?php endif; ?>
                </tr>
                <?php
            }
        ?>
    </tbody>
</table>

<?php else: ?>
<p> Pas de contrats </p>
<?php endif; ?>

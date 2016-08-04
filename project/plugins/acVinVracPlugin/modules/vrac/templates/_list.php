<?php use_helper('Float'); ?>
<?php use_helper('Vrac'); ?>
<?php use_helper('Date'); ?>

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
        <th>&nbsp;</th>
            <th style="width: 110px;">Date</th>
            <th>Soussignés</th>
            <th>Produit (Millésime)</th>
            <th style="width: 50px;">Vol.&nbsp;prop. <?php echo (!isset($teledeclaration) || !$teledeclaration)? "(Vol.&nbsp;enl.)" : "" ?></th>
            <th style="width: 50px;">Prix</th>
            <th style="width: 90px;"></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($vracs->rows as $value) {
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
                        <span class="<?php echo typeToPictoCssClass($v->type_transaction) ?>" style="font-size: 24px;"></span>
                        <?php if($v->valide->statut): ?>
                        <a href="<?php echo url_for('vrac_visualisation', array('numero_contrat' => $v->numero_contrat)) ?>">
                        <?php else: ?>
                        <a href="<?php echo url_for('vrac_redirect_saisie', array('numero_contrat' => $v->numero_contrat)) ?>">
                        <?php endif; ?>
                        <?php if($v->numero_archive): ?><?php echo $v->numero_archive ?><?php elseif(!$v->valide->statut): ?>Brouillon<?php else: ?>Non visé<?php endif; ?>
                        </a>
                        <br />
                        <?php if($v && isset($v->teledeclare) && $v->teledeclare): ?>
                        Télédeclaré
                        <?php endif; ?>
                        <span class="text-muted" style="font-size: 12px;"><?php echo formatNumeroBordereau($v->numero_contrat) ?></span>
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
        echo ($v->vendeur_identifiant) ?
                'Vendeur : ' . link_to($v->vendeur->nom, 'vrac/recherche?identifiant=' . preg_replace('/ETABLISSEMENT-/', '', $v->vendeur_identifiant)) : '';
        ?>
        <br />
        <?php
        echo ($v->acheteur_identifiant) ?
                'Acheteur : ' . link_to($v->acheteur->nom, 'vrac/recherche?identifiant=' . preg_replace('/ETABLISSEMENT-/', '', $v->acheteur_identifiant)) : '';
            ?>
        <?php
            $has_representant = ($v->representant_identifiant != $v->vendeur_identifiant) ? $v->representant_identifiant : 0;
            if ($has_representant) echo '<br/>';
            echo ($has_representant) ?
                'Representant : ' . link_to($v->representant->nom, 'vrac/recherche?identifiant=' . preg_replace('/ETABLISSEMENT-/', '', $v->representant_identifiant)) : '';
            ?>
        <?php if($v->mandataire_identifiant): ?>
            <br />
        <?php
        echo ($v->mandataire_identifiant) ?
                'Courtier : ' . link_to($v->mandataire->nom, 'vrac/recherche?identifiant=' . preg_replace('/ETABLISSEMENT-/', '', $v->mandataire_identifiant)) : '';
        ?>
                            </li>
        <?php endif; ?>
                        </ul>
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
            echo '<span class="text-muted">';
            if(!isset($teledeclaration) || !$teledeclaration){
            if ($v->volume_enleve) {
                echoFloat($v->volume_enleve);
                echo '&nbsp;'.VracConfiguration::getInstance()->getUnites()[$v->type_transaction]['volume_vigueur']['libelle'];
            }else{
                echo '0.00&nbsp;'.VracConfiguration::getInstance()->getUnites()[$v->type_transaction]['volume_vigueur']['libelle'];
            }
            echo '</span>';
          }
        }
        ?>
                    </td>
                    <td class="text-right">

        <?php if (isset($v->prix_initial_unitaire_hl)) {
                echoFloat($v->prix_initial_unitaire_hl);
                echo "&nbsp;".VracConfiguration::getInstance()->getUnites()[$v->type_transaction]['prix_initial_unitaire']['libelle'] ;
            }
        ?>
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
                          <?php if($v->valide->statut): ?>
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

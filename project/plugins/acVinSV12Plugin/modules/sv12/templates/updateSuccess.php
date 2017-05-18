<?php use_helper('SV12'); ?>

<?php include_partial('sv12/breadcrumb', array('sv12' => $sv12)); ?>
<?php
$types_contrats = array_merge(VracClient::getTypes(), array(SV12Client::SV12_TYPEKEY_VENDANGE => 'Contrat de vendanges'));
?>
<section id="principal" class="sv12">
    <?php include_partial('sv12/etapes', array('sv12' => $sv12, 'etape' => 'saisie')); ?>
    <form name="sv12_update" class="form-horizontal" method="POST" action="<?php echo url_for('sv12_update', $sv12); ?>" >
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <?php /*include_partial('global/hamzaStyle', array('table_selector' => '#table_contrats',
                                                             'mots' => contrat_get_words($sv12->contrats),
                                                             'consigne' => "Saisissez un produit, un numéro de contrat, un viticulteur ou un type (moût / raisin) :"))*/ ?>

        <table id="table_contrats" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="col-sm-4">Viticulteur </th>
                    <th class="col-sm-3">Produit</th>
                    <th class="col-sm-3">Contrat</th>
                    <th class="col-sm-2">Volume</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!count($sv12->contrats)): ?>
                <tr class="vide">
                    <td colspan="4">Aucune de ligne de SV12</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($sv12->contrats as $contrat) : ?>
                    <tr id="<?php echo contrat_get_id($contrat) ?>" class="<?php if($contrat->volume){echo "saisi";} ?>">
                        <td><?php if ($contrat->vendeur_identifiant): ?><?php echo $contrat->vendeur_nom . ' (' . $contrat->vendeur_identifiant . ')'; ?><?php else: ?>-<?php endif; ?></td>
                        <td><?php echo $contrat->produit_libelle; ?></td>
                        <td>
                            <?php if (!$contrat->contrat_numero && $contrat->contrat_type): ?>
                                <?php echo $types_contrats[$contrat->contrat_type]; ?>
                            <?php elseif($contrat->contrat_numero): ?>
                                <a href="<?php echo url_for(array('sf_route' => 'vrac_visualisation', 'numero_contrat' => $contrat->contrat_numero)) ?>"><?php echo $contrat->numero_archive; ?></a>
                                <?php echo sprintf('(%s,&nbsp;%s&nbsp;hl)', $contrat->getContratTypeLibelle(), $contrat->volume_prop); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            echo $form[$contrat->getKey()]->renderError();
                            echo $form[$contrat->getKey()]->render();
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <input name="addproduit" type="submit" tabindex="-1" class="btn btn-default" autofocus="autofocus" value="Ajouter un produit" />

        <div class="row" style="margin-top: 20px;">
            <div class="col-xs-4 text-left">
                <a tabindex="-1" href="<?php echo url_for('sv12_etablissement', array('identifiant' => $sv12->getEtablissement()->identifiant)); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour à l'espace SV12 de l'opérateur</a>
            </div>
            <div class="col-xs-4 text-center">
            </div>
            <div class="col-xs-4 text-right">
                <button type="submit" class="btn btn-success">Étape suivante <span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
        </div>
    </form>
</section>

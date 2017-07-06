<?php use_helper('PointsAides'); ?>
<?php foreach ($certificationsProduits as $certificationHash => $certificationProduits): ?>
    <?php $certifKey = $certificationProduits->certification_libelle; ?>
        <div class="col-xs-12">
            <h3><?php echo $certificationProduits->certification_libelle; ?></h3>
            <table id="table_drm_choix_produit" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="col-xs-4 text-left">Produits<?php echo getPointAideHtml('drm','produits') ?>
                             <a data-form="#form_choix_produits" href="<?php echo url_for('drm_choix_produit', array('sf_subject' => $drm, 'add_produit' => $certificationProduits->certification_keys)) ?>" value="" class="btn btn-default btn-xs link-submit pull-right"><span class="glyphicon glyphicon-plus-sign"></span> Ajouter un produit</a>
                        </th>
                        <th class="col-xs-4 text-center">
                          <div class="col-xs-10">
                          Déclarer des mouvements de produits<br />détenus en droits suspendus
                          </div>
                          <div class="col-xs-2">
                          <?php echo getPointAideHtml('drm','produits_coche_suspendu') ?>
                          </div>
                        </th>
                        <th class="col-xs-4 text-center">
                          <div class="col-xs-10">
                          Déclarer des mouvements de produits<br /> détenus en droits acquittés
                          </div>
                          <div class="col-xs-2">
                            <?php echo getPointAideHtml('drm','produits_coche_acquitte') ?>
                          </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="table_produit_body">
                    <?php if (count($certificationProduits->produits)): ?>
                        <?php foreach ($certificationProduits->produits as $produit):
                            ?>
                            <tr>
                                <td class="text-left"><?php echo $produit->getLibelle("%format_libelle%"); ?></td>
                                <td class="pointer text-center"><?php echo $form['produit' . $produit->getHashForKey()]->render(array('class' => 'checkbox_produit_'.$produit->getHashForKey())); ?></td>
                                <td class="pointer text-center"><?php echo $form['acquitte' . $produit->getHashForKey()]->render(array('class' => 'checkbox_acquitte_'.$produit->getHashForKey())); ?></td>
                            </tr>
                            <?php ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td class="text-center" colspan="3"><em>Vous n'avez pas de produit déclaré en catégorie <?php echo $certificationProduits->certification_libelle; ?></em> : <a data-form="#form_choix_produits" href="<?php echo url_for('drm_choix_produit', array('sf_subject' => $drm, 'add_produit' => $certificationProduits->certification_keys)) ?>" value="" class="btn btn-link btn-xs link-submit"><span class="glyphicon glyphicon-plus-sign"></span> Ajouter un produit</a></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
<?php endforeach; ?>

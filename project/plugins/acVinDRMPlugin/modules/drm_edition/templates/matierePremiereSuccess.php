<?php include_partial('drm/breadcrumb', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<section id="principal" class="drm">
    <?php include_partial('drm/etapes', array('drm' => $drm, 'isTeledeclarationMode' => $isTeledeclarationMode, 'etape_courante' => DRMClient::ETAPE_MATIERE_PREMIERE)); ?>

    <div id="application_drm">
        <form id="form_matiere_premiere" class="form-horizontal" action="<?php echo url_for('drm_matiere_premiere', $drm); ?>" method="post">
            <?php echo $form->renderGlobalErrors(); ?>
            <?php echo $form->renderHiddenFields(); ?>

            <p>Pour les Armagnac non conditionnés, les mouvements d'entrées et de sorties peuvent être renseignés en HLAP en choisissant le produit "MATIÈRES PREMIÈRES SPIRITUEUX".</p>
            <p>Pour les Armagnac conditionnés, les mouvements d'entrées et de sorties doivent être renseignés en HL (et non en HLAP) dans l'étape suivante des "mouvements suspendus".</p>
            <p>Cette étape vous permet de réaliser le transfert de matières premiers en volumes conditionnés. La conversion des HLAP en HL se calcule alors automatiquement.</p>
            <p>Vous retrouverez ces sorties de matières premières et ces entrées de volumes conditionnés dans chacun des produits l'étape suivante.</p>
            <?php foreach ($form->getDetailsMp() as $detailMpKey => $detailMp): ?>
            <h3><?php echo $detailMp->getLibelle();?></h3>
            <div class="form-group">
                <?php echo $form['stocks_debut_'.$detailMpKey]->renderError(); ?>
                <?php echo $form['stocks_debut_'.$detailMpKey]->renderLabel("Stock de matière première :", array("class" => "col-sm-4 control-label", "style" => "text-align: left; font-weight: normal;")); ?>
                <div class="col-sm-2">
                    <div class="input-group">
                    <?php echo $form['stocks_debut_'.$detailMpKey]->render(); ?>
                    <span class="input-group-addon"> hlap</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label" style="text-align: left; font-weight: normal;">Sorties bouteilles : </label>
            </div>
            <?php foreach($form['sorties_'.$detailMpKey] as $key => $item): ?>
              <?php
              $hlaptohl_key = md5($key);
              $splittedKey = explode("-",$key);
              ?>
                <?php echo $item['volume']->renderError(); ?>
                <?php echo $item['tav']->renderError(); ?>
                <div class="form-group volumehlaptohl">
                    <div class="col-sm-1">
                    </div>
                    <?php echo $item['volume']->renderLabel($drm->get($splittedKey[1])->getLibelle()." :", array("class" => "col-sm-3 control-label", "style" => "text-align: left; font-weight: normal;")); ?>
                    <div class="col-sm-2">
                        <div class="input-group">
                            <?php echo $item['volume']->render(array("data-volumehlap" => $hlaptohl_key)); ?>
                            <span class="input-group-addon"> hlap</span>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="input-group">
                        <?php echo $item['tav']->render(array("data-tav" => $hlaptohl_key)); ?>
                        <span class="input-group-addon"> °</span>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <p class="form-control-static"><span id="<?php echo $item['volume']->renderId()."_hl"; ?>" data-volumehl="<?php echo $hlaptohl_key; ?>"></span> hl</p>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="form-group volumehlaptohl">
                <div class="col-sm-1">
                </div>
                <div class="col-sm-2">
                <button name="add_produit" value="add_produit" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-plus-sign"></span> Ajouter un produit</button>
                </div>
            </div>
          <?php endforeach; ?>
            <div class="row" style="margin-top: 40px;">
                <div class="col-xs-4 text-left">
                    <a tabindex="-1" href="<?php echo ($isTeledeclarationMode) ? url_for('drm_choix_produit', $drm) :   url_for('drm_etablissement', $drm); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Etape précédente</a>
                </div>
                <div class="col-xs-4 text-center">
                    <?php if (!$isTeledeclarationMode): ?>
                        <a href="<?php echo url_for('drm_etablissement', $drm->getEtablissement()); ?>" class="btn btn-default">Enregistrer en brouillon</a>
                    <?php endif; ?>
                    <a class="btn btn-default" data-toggle="modal" data-target="#drm_delete_popup" >Supprimer la DRM</a>
                </div>
                <div class="col-xs-4 text-right">
                    <button type="submit" class="btn btn-success">Étape suivante <span class="glyphicon glyphicon-chevron-right"></span></button>
                </div>
            </div>
        </form>
    </div>
    <?php if(isset($formAddProduitsByCertification)): ?>
        <?php include_partial('drm_ajout_produit/ajout_produit_popup_certification', array('drm' => $drm, 'form' => $formAddProduitsByCertification, 'matiere_premiere' => true)); ?>
    <?php endif; ?>
</section>
<?php include_partial('drm/deleteDrmPopup', array('drm' => $drm, 'deleteForm' => $deleteForm)); ?>

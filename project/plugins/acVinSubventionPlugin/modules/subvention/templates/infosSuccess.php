<?php include_partial('subvention/breadcrumb', array('subvention' => $subvention, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<section id="principal">
    <?php include_partial('subvention/etapes', array('subvention' => $subvention)); ?>

    <form class="form-horizontal" method="POST" action="">

        <?php echo $form->renderGlobalErrors(); ?>
        <?php echo $form->renderHiddenFields(); ?>

        <h2>Identification du demandeur</h2>
        <p>Dans cette étape, vous devez saisir des informations de votre dossier « Contrat Relance Viti »</p>
        <div class="row">
            <div class="col-xs-9">
                <h3 style="margin-top: 5px;">Entreprise</h3>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">Raison sociale</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo $subvention->declarant->raison_sociale ?></p>
                   </div>
                </div>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">Famille</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo ($subvention->declarant->famille)? EtablissementFamilles::getFamilleLibelle($subvention->declarant->famille) : null; ?></p>
                   </div>
                </div>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">SIRET</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo $subvention->declarant->siret ?></p>
                   </div>
                </div>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">Adresse</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo $subvention->declarant->adresse ?></p>
                   </div>
                </div>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">Code postal</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo $subvention->declarant->code_postal ?></p>
                   </div>
                </div>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">Commune</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo $subvention->declarant->commune ?></p>
                   </div>
                </div>

                <?php foreach($form as $categorie => $items): ?>
            <?php if(!$items instanceof sfFormFieldSchema): continue; endif; ?>
            <hr style="margin-bottom: 0;" />
            <h3 style="margin-bottom: 20px; margin-top: 15px;"><?php echo $subvention->infos->get($categorie)->getLibelle() ?></h3>
            <?php foreach($items as $key => $item): ?>
                <div class="form-group">
                   <?php echo $item->renderError(); ?>
                   <?php echo $item->renderLabel(null, array("class" => "col-sm-3 control-label")); ?>
                   <div class="<?php if(get_class($item->getWidget()) == "bsWidgetFormInputFloat"): ?>col-sm-3<?php else: ?>col-sm-4<?php endif;?>">
                        <?php $unite = $subvention->infos->get($categorie)->getSchemaItem($key, "unite") ?>
                        <?php if($unite): ?><div class="input-group"><?php endif ?>
                        <?php echo $item->render(); ?>
                        <?php if($unite): ?>
                            <span class="input-group-addon"><?php echo $unite; ?></span>
                            </div>
                        <?php endif; ?>
                   </div>
                   <div class="col-sm-3">
                       <?php echo $item->renderHelp(); ?>
                   </div>
                </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
            </div>

            <div class="col-xs-3">
                <?php include_partial('subvention/aide'); ?>
            </div>
        </div>
        <div class="row" style="margin-top: 20px;">
            <div class="col-xs-6">
                <a class="btn btn-default" tabindex="-1" href=""><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Étape précédente</a>
            </div>
            <div class="col-xs-6 text-right">
                <button type="submit" class="btn btn-success">Étape suivante&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
        </div>
    </form>

</div>

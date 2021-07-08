<?php use_helper('Date'); ?>
<?php include_partial('dsnegoce/preTemplate'); ?>
<?php include_partial('dsnegoce/breadcrum', array('etablissement' => $etablissement)); ?>

<section id="principal">
    <?php include_partial('dsnegoce/etapes', array('dsnegoce' => $dsnegoce)); ?>

    <div class="form-horizontal">

        <p>Dans cette étape, vous devez confirmer les informations ci-dessous :</p>
        <div class="row">
            <div class="col-xs-9">
                <h3 style="margin-top: 5px;">Entreprise</h3>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">Raison sociale</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo $dsnegoce->declarant->raison_sociale ?></p>
                   </div>
                </div>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">Famille</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo ($dsnegoce->declarant->famille)? EtablissementFamilles::getFamilleLibelle($dsnegoce->declarant->famille) : null; ?></p>
                   </div>
                </div>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">CVI</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo $dsnegoce->declarant->cvi ?></p>
                   </div>
                </div>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">Adresse</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo $dsnegoce->declarant->adresse ?></p>
                   </div>
                </div>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">Code postal</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo $dsnegoce->declarant->code_postal ?></p>
                   </div>
                </div>
                <div class="form-group" style="margin-bottom: 0">
                   <label class="col-sm-3 control-label">Commune</label>
                   <div class="col-sm-8">
                        <p class="form-control-static" style="padding-bottom: 0; min-height: inherit;"><?php echo $dsnegoce->declarant->commune ?></p>
                   </div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 20px;">
            <div class="col-xs-6">
                <a class="btn btn-default" tabindex="-1" href="<?php echo url_for('dsnegoce_etablissement', $etablissement) ?>"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Étape précédente</a>
            </div>
            <div class="col-xs-6 text-right">
                <a class="btn btn-primary" href="<?php echo url_for('dsnegoce_stocks', $dsnegoce) ?>">Étape suivante&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
            </div>
        </div>
    </div>


</div>

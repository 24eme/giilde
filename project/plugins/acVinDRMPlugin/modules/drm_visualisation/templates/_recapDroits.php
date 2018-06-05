<?php use_helper('Float'); ?>
<?php use_helper('PointsAides'); ?>
<?php if ($isTeledeclarationMode) : ?>
  <h3>Droits et cotisations</h3>
<?php endif; ?>
<div class="row">
  <div class="col-xs-12">
    <p><?php echo getPointAideText('drm','visualisation_cvo'); ?></p>
  </div>
    <div class="col-xs-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title text-center">CVO<br />&nbsp;</h3>
            </div>
            <table id="table_drm_cvo_recap" class="table table-striped table-bordered table-condensed">
                <thead >
                    <tr>
                        <th>&nbsp;</th>
                        <th class="text-right">Volumes facturables<?php echo getPointAideHtml('drm','visualisation_cvo_volume_fact'); ?></th>
        <?php if($recapCvos["TOTAL"]->totalVolumeReintegration) : ?>
                        <th class="text-right">Volumes réintégrés</th>
        <?php endif; ?>
                        <th class="text-right">Montant<?php echo getPointAideHtml('drm','visualisation_cvo_montant'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($recapCvos as $key => $recapCvo): ?>
                    <tr style="<?php echo ($key == 'TOTAL')? 'font-weight:bold;' : ''; ?>" >
                        <td><?php if($recapCvo->version): ?><small class="text-muted"><?php echo $recapCvo->version ?></small> <?php endif; ?>CVO<?php echo ($recapCvo->version)? '' : ' Totale'; ?></td>
                        <td class="text-right"><?php
                            echoFloat($recapCvo->totalVolumeDroitsCvo);
                            echo " hl";
                            ?></td>
        <?php if($recapCvo->totalVolumeReintegration) : ?>
                        <td class="text-right"><?php
                            echoFloat($recapCvo->totalVolumeReintegration);
                            echo " hl";
                            ?></td>
        <?php endif; ?>
                        <td class="text-right"><?php
                            echoFloat($recapCvo->totalPrixDroitCvo);
                            echo " €";
                            ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php if ($isTeledeclarationMode) : ?>
    <div class="col-xs-7">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title text-center">Droits de circulation <br /><small class="text-muted">(des vins tranquilles et mousseux uniquement)</small></h3>
            </div>
            <table class="table table-bordered table-striped table-condensed">
                <thead >
                    <tr>
                        <th>Libellé</th>
                        <th>Code</th>
                        <th>Volumes imposables<?php echo getPointAideHtml('drm','visualisation_droit_volume'); ?></th>
                        <th>Taux<?php echo getPointAideHtml('drm','visualisation_droit_taux'); ?></th>
                        <th>Montant<?php echo getPointAideHtml('drm','visualisation_droit_montant'); ?></th>
                        <?php if ($drm->isPaiementAnnualise()): ?>
                            <th>Cumul annuel</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($drm->getDroitsDouane() as $droitDouane): ?>
                        <tr>
                            <td><?php echo $droitDouane->libelle; ?></td>
                            <td><?php echo $droitDouane->code; ?></td>
                            <td><?php echoFloat($droitDouane->volume_taxe - $droitDouane->volume_reintegre); echo " hl" ?></td>
                            <td><?php
                                echoFloat($droitDouane->taux);
                                echo " €/hl";
                                ?></td>
                            <td><?php
                                echoFloat($droitDouane->total);
                                echo " €";
                                ?></td>
                                <?php if ($drm->isPaiementAnnualise()): ?>
                            <td><?php
                                echoFloat($droitDouane->cumul);
                                echo " €";
                                ?></td>
            <?php endif; ?>
                        </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
</div>

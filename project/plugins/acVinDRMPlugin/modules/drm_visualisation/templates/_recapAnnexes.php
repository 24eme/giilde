<?php use_helper('Float'); ?>
<?php use_helper('PointsAides'); ?>
<?php if (count($drm->documents_annexes) || ($drm->exist('releve_non_apurement') && count($drm->releve_non_apurement)) || $drm->quantite_sucre || $drm->hasObservations() || $drm->hasTavs()): ?>
    <div class="row">
        <div class="col-xs-12">

            <h3>Annexes</h3>
            <ul class="list-group">
                <?php foreach ($drm->documents_annexes as $typeDoc => $numsDoc): ?>
                    <li class="list-group-item"><strong>Documents d'accompagnements :</strong> <?php echo DRMClient::$drm_documents_daccompagnement[$typeDoc]; ?> du n°<?php echo $numsDoc->debut; ?> au <?php echo $numsDoc->fin; ?> pour <?php echo $numsDoc->nb; ?> document(s)</li>
                <?php endforeach; ?>
                <?php
                if ($drm->exist('releve_non_apurement')):
                    foreach ($drm->releve_non_apurement as $num_non_apurement => $non_apurement):
                        ?>
                        <li class="list-group-item"><strong>Relevé de non apurement :</strong> n°<?php echo $non_apurement->numero_document; ?> expédié le <?php echo $non_apurement->date_emission; ?> pour le n° d'accise <?php echo $non_apurement->numero_accise; ?></li>
                    <?php
                    endforeach;
                endif;
                ?>
                <?php
                foreach ($drm->getObservationsArray() as $produitLibelle => $observation): ?>
                    <li class="list-group-item"><strong>Observation <?php echo $produitLibelle; ?> :</strong> <?php echo $observation; ?> </li>
                <?php
                endforeach;
                ?>

                  <li class="list-group-item"><h4>TAV enregistrée(s) :</h4></li>
                <?php
                  foreach ($drm->getTavsArray() as $produitLibelle => $tav):
                ?>
                    <li class="list-group-item">
                      <div class="row">
                      <div class="col-xs-8">
                        <strong><?php echo $produitLibelle; ?> </strong>
                      </div>
                      <div class="col-xs-4 text-right">
                        <?php echoFloat($tav); ?>
                      </div>
                    </div>
                    </li>
                <?php
                endforeach;
                foreach ($drm->getReplacementDateArray() as $produitLibelle => $date): ?>
                    <li class="list-group-item"><strong>Replacement de <?php echo $produitLibelle; ?> sorti en date du </strong> <?php echo $date; ?> </li>
                <?php endforeach; if ($drm->quantite_sucre): ?>
                    <li class="list-group-item"><strong>Quantité de sucres :</strong> <?php echo $drm->quantite_sucre ?> quintals</li>
                    <?php endif; ?>
                    <?php if ($drm->observations): ?>
                    <li class="list-group-item"><strong>Observations sur les mouvements :</strong> <?php echo $drm->observations; ?></li>
    <?php endif; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

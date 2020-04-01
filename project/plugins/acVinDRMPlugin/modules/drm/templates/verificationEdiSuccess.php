<section id="principal">
    <?php if(!$drm->isNew()): ?>
    <h2>Bilan de l'import de la DRM de <?php echo $drm->getHumanPeriode(); ?> (<?php echo $drm->identifiant ?>)</h2>
    <?php else: ?>
    <h2>Rapport d'erreurs de l'import de la DRM</h2>
    <?php endif; ?>
    <?php if (count($csvDoc->erreurs)): ?>
    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th>Numéro de ligne</th>
                <th>Erreur</th>
                <th>Raison</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($erreurs as $erreur) : ?>
            <tr class="<?php echo (isset($csvDoc) && $csvDoc->getStatut() == DRMCsvEdi::STATUT_ERREUR) ? "danger" : "warning"; ?>">
                <td><?php echo $erreur->num_ligne; ?></td>
                <td><?php echo $erreur->csv_erreur; ?></td>
                <td><?php echo $erreur->diagnostic; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php elseif(!$drm->isNew()): ?>
        <p style="margin-bottom: 40px;margin-top: 30px;">Le fichier a été intégré sans problème.</p>
    <?php else: ?>
        <p style="margin-bottom: 40px;margin-top: 30px;">Le fichier peut être integré sans problème.</p>
    <?php endif; ?>
    <div class="row">
        <div class="col-xs-4">
            <?php if($drm->isNew()): ?>
            <a class="btn btn-default" href="<?php echo url_for('drm_etablissement', array('identifiant' => $identifiant)); ?>">Annuler</a>
            <?php elseif($drm->isValidee()): ?>
            <a class="btn btn-default" href="<?php echo url_for('drm_visualisation', $drm); ?>"><span class="glyphicon glyphicon-chevron-left"></span> Précédent</a>
            <?php else: ?>
                <a class="btn btn-default" href="<?php echo url_for('drm_validation', $drm); ?>"><span class="glyphicon glyphicon-chevron-left"></span> Précédent</a>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 text-center">
            <a class="btn <?php if($drm->isNew()): ?>btn-default<?php else: ?>btn-primary<?php endif; ?>" href="<?php echo url_for('drm_csv_edi', array('identifiant' => $csvDoc->identifiant, 'periode' => $csvDoc->periode)) ?>">Télécharger le fichier versé</a>
        </div>
        <div class="col-xs-4">
        <?php if ($drm->isNew() && isset($csvDoc) && $csvDoc->getStatut() != DRMCsvEdi::STATUT_ERREUR): ?>
            <a href="<?php echo url_for('drm_creation_fichier_edi', array('periode' => $periode, 'md5' => $md5,'identifiant' => $identifiant)); ?>" class="btn btn-success" style="float: right;">Importer la DRM</a>
        <?php endif; ?>
        </div>
    </div>
</section>

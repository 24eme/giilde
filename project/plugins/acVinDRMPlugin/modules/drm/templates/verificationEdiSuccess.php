<section id="principal">
    <h2>Rapport d'erreurs de l'import de la DRM</h2>
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
            <tr class="<?php echo ($drmCsvEdi->getCsvDoc()->getStatut() == DRMCsvEdi::STATUT_ERREUR) ? "danger" : "warning"; ?>">
                <td><?php echo $erreur->num_ligne; ?></td>
                <td><?php echo $erreur->csv_erreur; ?></td>
                <td><?php echo $erreur->diagnostic; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="row">
        <div class="col-xs-6">
            <a class="btn btn-default" href="<?php echo url_for('drm_etablissement', array('identifiant' => $drm->getIdentifiant())); ?>"><span>Annuler</span></a>
        </div>
        <div class="col-xs-6">
        <?php if ($drmCsvEdi->getCsvDoc()->getStatut() != DRMCsvEdi::STATUT_ERREUR): ?>
            <a href="<?php echo url_for('drm_creation_fichier_edi', array('periode' => $periode, 'md5' => $md5,'identifiant' => $identifiant)); ?>" class="btn btn-success" style="float: right;">Importer la DRM</a>
        <?php endif; ?>
        </div>
    </div>
</section>

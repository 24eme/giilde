<ol class="breadcrumb">
    <li><a href="<?php echo url_for('vrac_upload_index') ?>">Importation</a></li>
    <li><a href="<?php echo url_for('vrac_upload_verification') ?>" class="active">Vérification</a></li>
</ol>

<h1>Rapport d'erreur sur l'import</h1>

<?php if ($warnings->count() > 0): ?>
    <div class="alert alert-warning" role="alert">
        <?php foreach ($warnings as $contrat => $contrat_warnings): ?>
        <p class="h4"><?= count($contrat_warnings) ?> <?= (count($contrat_warnings) > 1) ? 'avertissements' : 'avertissement' ?> à la ligne <?= $contrat ?> du CSV</p>
        <ul>
            <?php foreach ($contrat_warnings as $warning): ?>
                <li><?= $warning ?></li>
            <?php endforeach ?>
        </ul>
        <?php endforeach ?>
    </div>
<?php endif ?>

<?php if ($errors->count() === 0): ?>
    <div class="alert alert-success">
        Pas d'erreur détéctées. Vous pouvez y aller !
    </div>

    <form action="<?= url_for('vrac_upload_import') ?>" method="POST">
        <input type="hidden" name="md5" value="<?= $file->getMd5() ?>">

        <button class="btn btn-primary">Importer</button>
    </form>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ($errors as $contrat => $contrat_errors): ?>
        <p class="h4"><?= count($contrat_errors) ?> <?= (count($contrat_errors) > 1) ? 'erreurs' : 'erreur'?> à la ligne <?= $contrat ?> du CSV</p>
            <ul>
                <?php foreach ($contrat_errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach ?>
            </ul>
        <?php endforeach ?>
    </div>

    <a href="<?= url_for('vrac_upload_index') ?>" class="btn btn-primary center-block">Retour à l'upload</a>
<?php endif ?>

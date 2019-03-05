<ol class="breadcrumb">
    <li><a href="<?php echo url_for('vrac_upload_verification') ?>" class="active">Contrats</a></li>
</ol>

<h1>Rapport d'erreur sur l'import</h1>
<?php if ($verification->count() == 0): ?>
    <div class="alert alert-success">
        Pas d'erreur détéctées. Vous pouvez y aller !
    </div>

    <form action="<?= url_for('vrac_upload_import') ?>" method="POST">
        <input type="hidden" name="md5" value="<?= $file->getMd5() ?>">

        <button class="btn btn-primary">Importer</button>
    </form>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ($verification as $contrat => $erreurs): ?>
        <p class="h4"><?= count($erreurs) ?> <?= (count($erreurs) > 1) ? 'erreurs' : 'erreur'?> à la ligne <?= $contrat ?> du CSV</p>
            <ul>
                <?php foreach ($erreurs as $erreur): ?>
                    <li><?= $erreur ?></li>
                <?php endforeach ?>
            </ul>
        <?php endforeach ?>
    </div>

    <a href="<?= url_for('vrac') ?>" class="btn btn-primary center-block">Retour à l'upload</a>
<?php endif ?>

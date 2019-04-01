<ol class="breadcrumb">
    <li><a href="<?= url_for('vrac') ?>">Contrats</a></li>
    <li><a href="<?= url_for('vrac_upload_index') ?>" class="active">Importer</a></li>
</ol>

<div class="col-xs-12">
    <h3>Téléversement d'un fichier CSV</h3>
    <?= $uploadForm->renderFormTag(url_for('vrac_upload_verification')) ?>
        <?= $uploadForm ?>
        <button type="submit" class="btn btn-default">Importer des contrats</button>
    </form>
</div>

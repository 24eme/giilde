<?php include_partial('subvention/breadcrumb', array('subvention' => $subvention, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<section id="principal">
    <?php include_partial('subvention/etapes', array('subvention' => $subvention)); ?>

    <h2>Détail des actions</h2>

    <p>Cette étape consiste à décrire les actions de promotion et communication que vous souhaitez mettre en place dans le cadre du Contrat Relance Viti.</p>

    <p>Pour ce faire, nous mettons à votre disposition un fichier Excel  (Etape A) qu’il convient de déposer sur cette page après l’avoir complété (Etape B)</p>

    <form class="form-horizontal" role="form" action="<?php echo url_for('subvention_dossier', array('identifiant' => $subvention->identifiant,'operation' => $subvention->operation)) ?>" method="post" enctype="multipart/form-data">
      <?php echo $form->renderGlobalErrors(); ?>
      <?php echo $form->renderHiddenFields(); ?>
    <div class="row">
        <div class="col-xs-9">
            <div>
            <h3>Étape A</h3>
            <a class="btn btn-default btn-lg"
               style="width: 100%; text-align: left;"
               <?php if(!$subvention->hasXls() && !$subvention->hasDefaultXlsPath()): ?> disabled="disabled" <?php else: ?>
                 href="<?php echo url_for('subvention_xls', array('identifiant' => $subvention->identifiant,'operation' => $subvention->operation)) ?>"
               <?php endif; ?>
               >
               <span class="glyphicon glyphicon-file"></span> <?php if(!$subvention->hasXls()): ?>Télécharger le dossier vierge à compléter<?php else:?> Télécharger mon dossier déposé le <?php echo ($subvention->dossier_date)? DateTime::createFromFormat("Y-m-d H:i:s",$subvention->dossier_date)->format("d/m/Y à H\hi") : ''; ?><?php endif; ?>
            </a>
            </div>
            <hr />
            <div>
                <h3>Étape B</h3>
                <?php echo $form['file']->renderError() ?>
                <label class="btn btn-default btn-lg text-left" style="width: 100%; text-align: left;" for="subvention_dossier_file">
                    <?php echo $form['file']->render(array('style' => 'display:none', 'onchange' => "$('#upload-file-info').html(this.files[0].name)")) ?>
                    <span class="glyphicon glyphicon-download-alt"></span> <span id="upload-file-info"><?php if($subvention->hasXls()): ?>Remplacer le fichier complété<?php else: ?>Verser le fichier complété<?php endif ?></span>
                </label>
            </div>
        </div>
        <div class="col-xs-3">
            <?php include_partial('subvention/aide'); ?>
        </div>
    </div>
    <div style="margin-top: 30px;" class="row">
        <div class="col-xs-6">
            <a class="btn btn-default" tabindex="-1" href="<?php echo url_for('subvention_infos', $subvention); ?>"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Étape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-success">Étape suivante&nbsp;<span class="glyphicon glyphicon-chevron-right"></button>
        </div>
    </div>
    </form>
</section>

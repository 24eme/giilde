<?php include_partial('subvention/breadcrumb', array('subvention' => $subvention)); ?>

<section id="principal" class="form-horizontal">
    <h2>
    	Récapitulatif de votre dossier</strong>&nbsp;<small style="font-size: 14px;" class="text-muted">Version <?php echo $subvention->version; ?></small>
        <button class="btn btn-sm <?php if($subvention && $subvention->isApprouve()): ?>
        btn-success
        <?php elseif($subvention && $subvention->isRefuse()): ?>
        btn-danger
        <?php elseif($subvention && $subvention->isValide()): ?>
        btn-warning
        <?php else: ?>
        btn-default
        <?php endif; ?>"><?php echo $subvention->getStatutLibelle(); ?></button>
      <?php if($subvention->isValideInterpro()): ?>
        <a href="<?php echo url_for('subvention_reouvrir', $subvention) ?>" class="btn btn-warning pull-right">Ré-ouvrir la demande</a>
      <?php endif; ?>
    </h2>

  <?php include_partial('subvention/recap', array('subvention' => $subvention)); ?>

  <?php if(!$isTeledeclarationMode && isset($formValidationInterpro)): ?>
    <div class="text-center">
        <div class="btn-group" role="group">
            <a href="<?php echo url_for('subvention_pdf', $subvention) ?>" class="btn btn-default"><span class="glyphicon glyphicon-download-alt"></span>&nbsp;Télécharger le PDF</a>
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li><a href="<?php echo url_for('subvention_pdf', $subvention) ?>">Fiche de pré-qualification (PDF)</a></li>
                <li><a href="<?php echo url_for('subvention_xls', $subvention) ?>">Descriptif détaillé de l'opération (Excel)</a></li>
                <li><a href="<?php echo url_for('subvention_zip', $subvention) ?>">Dossier complet (ZIP)</a></li>
            </ul>
        </div>
    </div>
    <hr/>
    <h1>
      Approbation du dossier
    </h1>
    <div class="row row-condensed">
    	<div class="col-xs-12">
    		<form class="form-horizontal" method="POST" action="" id="approbationForm">
          <?php include_partial('subvention/validationInterpro', array('form' => $formValidationInterpro, 'subvention' => $subvention)); ?>
  <?php endif; ?>

    <div class="row">
        <div class="col-xs-4">
            <?php if(!$isTeledeclarationMode): ?>
            <a class="btn btn-default" tabindex="-1" href="<?php echo url_for('subvention') ?>"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Retour à mon espace</a>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 text-center">
            <div class="btn-group dropup" role="group">

                <?php if(!$isTeledeclarationMode && isset($formValidationInterpro)): ?>
                    <button type="submit" name="pdf" value="pdf" class="btn btn-default"><span class="glyphicon glyphicon-download-alt"></span>&nbsp;Enregistrer et voir le PDF</a>
                <?php else: ?>
                    <a href="<?php echo url_for('subvention_pdf', $subvention) ?>" class="btn btn-default"><span class="glyphicon glyphicon-download-alt"></span>&nbsp;Télécharger le PDF</a>
                <?php endif; ?>
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo url_for('subvention_pdf', $subvention) ?>">Fiche de pré-qualification (PDF)</a></li>
                    <li><a href="<?php echo url_for('subvention_xls', $subvention) ?>">Descriptif détaillé de l'opération (Excel)</a></li>
                    <li><a href="<?php echo url_for('subvention_zip', $subvention) ?>">Dossier complet (ZIP)</a></li>
                </ul>
            </div>
        </div>
        <div class="col-xs-4 text-right">
            <?php if(!$isTeledeclarationMode && isset($formValidationInterpro)): ?>
                <div class="btn-group dropup" role="group">
                    <button class="btn btn-success" type="submit" name="valider" value="valider"><span class="glyphicon glyphicon-ok"></span>&nbsp;Valider la préqualification</a>
            		<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>
                	<ul class="dropdown-menu">
                        <button class="btn" type="submit">Enregistrer en brouillon</button>
                    </ul>
    			</div>
            <?php else: ?>
              <a href="https://mesaidesenligne.laregion.fr/" target="_blank" class="btn btn-success">Vers le site de la région Occitanie&nbsp;<span class="glyphicon glyphicon-log-out"></span></a>
            <?php endif; ?>
        </div>
    </div>
    <?php if(!$isTeledeclarationMode && isset($formValidationInterpro)): ?>
        </form>
      </div>
    </div>
    <?php endif; ?>
</section>

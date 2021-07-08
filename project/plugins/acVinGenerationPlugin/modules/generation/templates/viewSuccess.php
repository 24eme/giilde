<?php use_helper("Generation"); ?>
<?php use_helper("Date"); ?>
<?php use_helper("Float"); ?>

<?php include_partial('facture/preTemplate'); ?>

<div class="page-header no-border">
    <div class="btn-group pull-right">
        <?php if($generation->statut == GenerationClient::GENERATION_STATUT_GENERE && GenerationClient::getInstance()->isRegenerable($generation)): ?>
        <a href="<?php // echo url_for('generation_regenerate', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission)); ?>" onclick='return confirm("Étes vous sûr de vouloir regénérer les factures ?");' class="btn btn-sm btn-default btn-default-step btn-upper"><span class="glyphicon glyphicon-repeat"></span>&nbsp;&nbsp;Regénérer</a>
        <?php endif; ?>
    </div>
    <h2>
      Génération N° <?= ($generation->getMasterGeneration()) ? $generation->getMasterGeneration()->identifiant.' '.$generation->type_document : $generation->identifiant; ?><small> créé le <?php echo GenerationClient::getInstance()->getDateFromIdGeneration($generation->date_maj); ?></small>
    </h2>
</div>

<?php if($generation->libelle): ?>
<p class="text-center lead">
    <?php echo $generation->libelle; ?>
</p>
<?php endif; ?>

<?php if(count($generation->arguments) > 0): ?>
<p class="text-center text-muted">
    <?php foreach ($generation->arguments as $key => $argument) : ?>
        <?php echo ucfirst(getLabelForKeyArgument($key)) ?></strong> <?php echo $argument; ?><br />
    <?php endforeach; ?>
</p>
<?php endif; ?>

<p class="text-center lead">
    <?php echo $generation->nb_documents; ?> document<?php if($generation->nb_documents > 1): ?>s<?php endif; ?>
    <?php if($generation->somme): ?><small class="text-muted">(<?php echo echoFloat($generation->somme) ?> € HT)</small><?php endif; ?>
</p>

<p class="text-center lead">
    <span class="label label-<?php echo statutToCssClass($generation->statut) ?>"><span class="<?php echo statutToIconCssClass($generation->statut) ?>"></span>&nbsp;&nbsp;<?php echo statutToLibelle($generation->statut); ?></span>
</p>

<p class="text-center">
<small class="text-muted">(Mis à jour le <?php echo GenerationClient::getInstance()->getDateFromIdGeneration($generation->date_maj); ?>)</small>
</p>

<?php if ($generation->message) : ?>
    <div class="alert alert-<?php if($generation->statut == GenerationClient::GENERATION_STATUT_ENERREUR): ?>danger<?php else: ?>warning<?php endif; ?>" style="max-height: 200px; overflow: auto">
        <?php echo nl2br($generation->message); ?>
    </div>
<?php endif; ?>

<?php if ($generation->statut == GenerationClient::GENERATION_STATUT_GENERE ) : ?>
<div class="row">
  <div class="col-xs-6 col-xs-offset-3">
    <?php foreach ($generation->fichiers as $chemin => $titre): ?>
      <p>
        <a download="<?php echo basename(urldecode($chemin)) ?>" href="<?php echo urldecode($chemin); ?>"  target="_blank" class="list-group-item text-center"><span class="glyphicon glyphicon-download-alt"></span>&nbsp;&nbsp;<?php echo $titre; ?></a>
      </p>
    <?php endforeach; ?>
    <?php foreach($sous_generations as $sous_generation): ?>
        <?php foreach ($sous_generation->fichiers as $chemin => $titre): ?>
            <p style="position: relative;">
                <a class="list-group-item text-center" download="<?php echo basename(urldecode($chemin)) ?>" href="<?php echo urldecode($chemin); ?>"  target="_blank"><span class="glyphicon glyphicon-download-alt"></span>&nbsp;&nbsp;<?php echo $titre; ?></a>
                <a class="btn btn-link" style="position: absolute; top: 5px; right: -40px" href="<?= url_for('generation_view', [
                  'type_document' => $generation->type_document,
                  'date_emission' => $generation->date_emission.'-'.$sous_generation->type_document
                ]) ?>"><span class="glyphicon glyphicon-eye-open"></span></a>
            </p>
        <?php endforeach; ?>
    <?php endforeach; ?>


    <?php if($generation->exist('sous_generation_types')): ?>
    <?php foreach ($generation->sous_generation_types as $sous_generation_type): ?>
        <p>
        <?php if (count($generation->getOrCreateSubGeneration($sous_generation_type)->fichiers)): continue; endif; ?>
          <a class="btn btn-link" href="<?= url_for('facture_sous_generation', [
            'generation' => $generation->_id,
            'type' => $sous_generation_type
          ]) ?>"><?php if($generation->getOrCreateSubGeneration($sous_generation_type)->isNew()): ?><span class="glyphicon glyphicon-play-circle"></span><?php else: ?><span class="glyphicon glyphicon-eye-open"></span><?php endif; ?>&nbsp;<?php echo call_user_func_array(array(GenerationClient::getClassForGeneration($generation->getOrCreateSubGeneration($sous_generation_type)), "getActionLibelle"), array()); ?></a>
        </p>
    <?php endforeach ?>
    <?php endif ?>
  </div>
</div>
<?php endif; ?>



<div class="row row-margin">
    <div class="col-xs-4 text-left">
        <?php if(isset($backUrl) && $backUrl): ?>
        <a class="btn btn-default btn-default-step btn-lg btn-upper" href="<?php echo $backUrl ?>"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
        <?php endif; ?>
    </div>
    <?php if(in_array($generation->statut, array(GenerationClient::GENERATION_STATUT_ENERREUR, GenerationClient::GENERATION_STATUT_GENERE)) && $generation->message): ?>
    <div class="col-xs-4 text-center">
        <a class="btn btn-<?php if($generation->statut == GenerationClient::GENERATION_STATUT_ENERREUR): ?>danger<?php else: ?>warning<?php endif; ?> btn-upper" href="<?php echo url_for('generation_reload', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission)); ?>"><span class="glyphicon glyphicon-refresh"></span>&nbsp;&nbsp;Relancer</a>
    </div>
    <?php elseif(in_array($generation->statut, array(GenerationClient::GENERATION_STATUT_ENERREUR)) && !$generation->message): ?>
    <div class="col-xs-4 text-center">
        <a class="btn btn-btn-default btn-default-step btn-upper" href="<?php echo url_for('generation_reload', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission)); ?>"><span class="glyphicon glyphicon-refresh"></span>&nbsp;&nbsp;Relancer</a>
    </div>
    <?php elseif(GenerationClient::getInstance()->isRegenerable($generation) && in_array($generation->statut, array(GenerationClient::GENERATION_STATUT_GENERE)) && !$generation->message): ?>
    <div class="col-xs-4 text-center">
        <a class="btn btn-btn-default btn-default-step btn-upper" href="<?php echo url_for('generation_reload', array('type_document' => $generation->type_document, 'date_emission' => $generation->date_emission)); ?>"><span class="glyphicon glyphicon-refresh"></span>&nbsp;&nbsp;Relancer</a>
    </div>
    <?php endif; ?>
</div>

<?php if ($generation->getMasterGeneration()): ?>
<a href="<?= url_for('generation_view', [
  'type_document' => $generation->getMasterGeneration()->type_document,
  'date_emission' => $generation->getMasterGeneration()->date_emission
]) ?>" class="btn btn-default"><i class="glyphicon glyphicon-chevron-left"></i> Retour</a>
<?php endif ?>


<?php if(in_array($generation->statut, array(GenerationClient::GENERATION_STATUT_ENATTENTE, GenerationClient::GENERATION_STATUT_ENCOURS))): ?>
<script type="text/javascript">window.setTimeout("window.location.reload()", 30000);</script>
<?php endif; ?>

<?php include_partial('facture/postTemplate'); ?>
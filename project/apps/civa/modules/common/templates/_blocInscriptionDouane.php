<?php $interpro = sfConfig::get('app_teledeclaration_interpro'); ?>


<div class="well">
    <p>Pour adhérer au service de télétransmission de vos DRM à l’application CIEL du Portail des Douanes, vous devez télécharger le Bulletin d’Adhésion à CIEL en cliquant sur le bouton ci-dessous.</p>
    <p>Après l'avoir complété et signé, vous devez le renvoyer en 2 exemplaires au CIVA.</p>
    <div class="text-center"><button type="button" class="btn btn-warning" data-toggle="modal" data-target="#convention_ciel">Adhésion à CIEL</button></div>
</div>

<div id="convention_ciel" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Adhésion à l’application CIEL du Portail des Douanes</h4>
      </div>
      <div class="modal-body">
        <p>Pour que nous puissions transmettre vos DRM aux Douanes par l’intermédiaire de l’application CIEL sue le Portail des Douanes,
          il est indispensable de préalablement compléter, signer et retourner au CIVA la <?php if (isset($calendrier) && $calendrier->isMultiEtablissement()): ?>convention d’adhésion à CIEL<?php else: ?><a href="<?php echo url_for('drm_convention', $etablissement) ?>">convention d’adhésion à CIEL</a><?php endif; ?>.</p>

        <p>Le CIVA se chargera de transmettre aux Douanes votre bulletin d’adhésion afin d’activer votre connexion au téléservice CIEL.</p>

        <p>Vous pouvez dès à présent <?php if (isset($calendrier) && $calendrier->isMultiEtablissement()): ?>télécharger ce bulletin d’adhésion<?php else: ?><a href="<?php echo url_for('drm_convention', $etablissement) ?>">télécharger ce bulletin d’adhésion</a><?php endif; ?>
          et le retourner au CIVA complété et signé, même si vous ne souhaitez dématérialiser votre DRM que dans quelques mois.</p>

      </div>
      <div class="modal-footer text-center">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Fermer</button>
          <?php if (isset($calendrier) && $calendrier->isMultiEtablissement()): ?>
          	<?php foreach ($calendrier->getEtablissements() as $etab): ?>
          	<a class="btn btn-info" href="<?php echo url_for('drm_convention', $etab) ?>" style="margin-bottom: 10px;">Télécharger la convention d'adhésion à CIEL<br /><small><?php echo $etab->nom ?> (<?php echo $etab->identifiant ?>)</small></a>
          	<?php endforeach; ?>
          <?php else: ?>
          <a class="btn btn-info" href="<?php echo url_for('drm_convention', $etablissement) ?>">Télécharger la convention d'adhésion à CIEL</a>
      	  <?php endif; ?>
      </div>
    </div>
  </div>
</div>

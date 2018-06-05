<?php
$args_url = array('identifiant' => $drm->identifiant, 'periode_version' => $drm->getPeriodeAndVersion());
if (isset($retour) && ($retour == 'crds')) {
    $args_url = array_merge($args_url, array('retour' => $retour));
}
$interpro = strtoupper(sfConfig::get('app_teledeclaration_interpro'));
?>

<div id="drm_choix_regime_crd_popup"  class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <form action="<?php echo url_for('drm_choix_regime_crd', $args_url) ?>" method="post">
              <?php echo $crdRegimeForm->renderHiddenFields(); ?>
              <?php echo $crdRegimeForm->renderGlobalErrors(); ?>
      <div class="modal-content">
          <div class="modal-header">
              <h2>Choisir un régime de CRD (Compte capsule)</h2>
          </div>
          <div class="modal-body">
            <br/>
            <p><span class="text-danger">Votre chai «&nbsp;<?php echo $drm->getEtablissement()->nom; ?>&nbsp;» ne possède actuellement aucun régime de CRD.</span></p><br/>
            <p>Il est nécessaire pour la suite de la saisie de choisir ici le régime CRD. Une fois choisi ce message n'apparaîtra plus.</p><br/>
            <?php if(DRMConfiguration::getInstance()->isCrdOnlySuspendus()): ?>
                 <p>Si vous utilisez des CRD personnalisées, cliquez sur «&nbsp;CRD personnalisées&nbsp;».</p><p>Pour les autres cas ou si vous n'avez pas de CRD, sélectionnez «&nbsp;CRD collectives ou Pas de CRD&nbsp;»</p>
            <?php else : ?>
               <p>Si vous achetez des CRD Acquitées auprès de votre ODG (ou auprès d'un autre répartiteur), sélectionnez «&nbsp;banalisées acquittées&nbsp;». Si vous utilisez des CRD personnalisées, cliquez sur «&nbsp;personnalisé&nbsp;». Pour les autres cas, sélectionnez «&nbsp;banalisées suspendues&nbsp;»</p>
           <?php endif; ?>
            <br/>
              <?php if(DRMConfiguration::getInstance()->isCrdOnlySuspendus()): ?>
                  <p>Sur la DRM papier du <?php echo $interpro; ?>, le régime CRD est indiqué&nbsp;:</p>
              <?php else : ?>
            <p>Sur la DRM papier de l'<?php echo $interpro; ?>, le régime CRD est demandé dans le cadre dédié au stock capsules&nbsp;:</p>
             <?php endif; ?>
            <center><img src="/images/visuels/regime_crd_papier.jpg" width="600" height="150" ></center>
            <br/>
            <p><b>Votre régime CRD&nbsp;:</b></p>

              <br/>
                  <?php echo $crdRegimeForm['crd_regime']->render(); ?>
              <br/>

          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Annuler</button>
              <button id="popup_confirm" type="submit" class="btn btn-success pull-right" ><span>Valider ce choix de régime CRD</span></button>
          </div>
      </div>
      </form>
  </div>
</div>

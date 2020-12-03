<?php if ($drm->exist('transmission_douane') && $drm->transmission_douane && (!$isTeledeclarationMode  || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())) && !$drm->isNegoce()): ?>
<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title text-center"<?php if (!$isTeledeclarationMode || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?> style="padding: 0 0 5px 0;"<?php endif; ?>>Transmission proDou@ane <?php if (!$isTeledeclarationMode || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?><a class="btn btn-warning btn-xs pull-right" href="<?php echo url_for('drm_edition_libelles', $drm) ?>">Modifier les libellés prodouane</a><?php endif; ?></h3>
        </div>
        <div class="panel-content>">
            <table class="table table-striped table-condensed">
                <tbody>
                  <tr>
                      <td class="col-xs-4">Transmission (<?php echo link_to('XML transmis', 'drm_xml_table', array("identifiant" => $drm->identifiant,"periode_version" => $drm->getPeriodeAndVersion(), "retour" => "0")); ?>)</td>
                      <td class="col-xs-8">
                        <?php if ($drm->transmission_douane->success) : ?>
                          <?php if ($drm->transmission_douane->id_declaration): ?>
                          La transmission a été réalisée avec <strong>succès</strong> le <?php echo $drm->getTransmissionDate(); ?> avec l'accusé reception numéro <?php echo $drm->transmission_douane->id_declaration ?>.
                          <?php else: ?>
                          La transmission a été <strong>validée</strong> malgré une erreur : « <?php echo $drm->getTransmissionErreur(); ?> ».
                          <?php endif; ?>
                        <?php elseif (preg_match('/HTTP Error 0/', $drm->getTransmissionErreur())) : ?>
                          Le service de reception des DRM de la Douane n'était disponible au moment de la transmission. Lorsque la liaison sera de nouveau disponible, une retransmission sera effectuée.
                        <?php else: ?>
                          La transmission a échouée. Le(s) message(s) d'erreur envoyé(s) par le portail des douanes sont :
                          <ul>
                            <?php foreach(explode('.', $drm->getTransmissionErreur()) as $li) if ($li) : ?>
                              <li> «&nbsp;<?php echo $li ?>&nbsp;» </li>
                            <?php endif; ?>
                          </ul>
                        <?php endif; ?>
                        <?php if (!$isTeledeclarationMode  || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?>
                          <a id="retransmission" data-link="<?php echo url_for('drm_retransmission', $drm); ?>" class="pull-right btn btn-xs btn-default" >retransmettre&nbsp;<span class="glyphicon glyphicon-repeat"></span></a>
                          <?php if (!$drm->transmission_douane->success): ?>
                          <a href="<?php echo url_for('drm_success_true', $drm); ?>" class="pull-right btn btn-xs btn-default" >ignore&nbsp;<span class="glyphicon glyphicon-eye-close"></span></a>
                          <?php endif; ?>
                        <?php endif; ?>
                      </td>
                  </tr>
                  <?php if (!$isTeledeclarationMode  || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())): ?>
                    <?php if (is_null($drm->transmission_douane->coherente)) : ?>
                      <tr><td>Retour XML</td><td>Aucun retour de la part de proDou@ne n'a été effectué<a href="<?php echo url_for('drm_retour_refresh', $drm); ?>"  class="pull-right btn btn-xs btn-default" ><span class="glyphicon glyphicon-repeat"></span></a></td></tr>
                    <?php elseif($drm->transmission_douane->coherente): ?>
                      <tr><td>
                          Retour XML (<a href="<?php echo url_for('drm_xml_table', array('identifiant' => $drm->identifiant,"periode_version" => $drm->getPeriodeAndVersion(), 'retour' => "1")); ?>">XML reçu</a>)</td><td>
                          <?php if ($drm->transmission_douane->coherente && (!$drm->transmission_douane->exist('diff') || !$drm->transmission_douane->diff)) :?>
                              La DRM est <strong>conforme</strong> à celle de proDou@ne.
                          <?php else: ?>
                              La DRM a été estimée <strong>conforme</strong> malgré des différences.
                          <?php endif; ?>
                              <a href="<?php echo url_for('drm_retour_refresh', $drm); ?>"  class="pull-right btn btn-xs btn-default" >revérifier&nbsp;<span class="glyphicon glyphicon-repeat"></span></a>
                          </td>
                      </tr>
                    <?php else: ?>
                      <tr>
                          <td>Retour XML (<a href="<?php echo url_for('drm_xml_table', array('identifiant' => $drm->identifiant,"periode_version" => $drm->getPeriodeAndVersion(), 'retour' => "1")); ?>">XML reçu</a>)</td><td>La DRM n'est <strong>pas conforme</strong> à celle de proDou@ne
                              <a href="<?php echo url_for('drm_retour_refresh', $drm); ?>"  class="pull-right btn btn-xs btn-default" >revérifier&nbsp;<span class="glyphicon glyphicon-repeat"></span></a>
                              <a href="<?php echo url_for('drm_retour_ignore', $drm); ?>"  class="pull-right btn btn-xs btn-default" >ignorer&nbsp;<span class="glyphicon glyphicon-eye-close"></span></a>
                          </td>
                      </tr>
                    <?php endif; ?>
                  <?php endif; ?>
                </tbody>
              </table>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

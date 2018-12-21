
<?php if ((!$isTeledeclarationMode  || (sfConfig::get('app_force_usurpation_mode') && $sf_user->isUsurpationCompte())) && $drm->exist('transmission_douane') && $drm->transmission_douane->coherente === false): ?>
<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title text-center">Rapport du retour XML proDou@ane</h3>
        </div>
        <div class="panel-content>">
            <table class="table table-striped table-condensed">
                <tbody>
                  <tr>
                    <td colspan="3"></td>
                    <td>Valeur <a href="<?php echo url_for('drm_xml_table', array('identifiant' => $drm->identifiant,"periode_version" => $drm->getPeriodeAndVersion(), 'retour' => "1")) ?>" >XML&nbsp;reçu</a></td>
                    <th>Valeur <a href="<?php echo url_for('drm_xml_table', array('identifiant' => $drm->identifiant,"periode_version" => $drm->getPeriodeAndVersion(), 'retour' => '0')) ?>" >XML&nbsp;transmis</a></th>
                  </tr>
                  <?php foreach ($drm->getXMLComparison()->getFormattedXMLComparaison() as $problemeSrc => $values): ?>
                      <?php preg_match('/^(\[.+\]) (.+)((entree|sortie|stock).+)$/', $problemeSrc, $matches); ?>
                    <tr>
                      <td><?php echo (isset($matches[1]))? $matches[1] : $problemeSrc; ?></td>
                      <td><?php echo (isset($matches[2]))? $matches[2] : ""; ?></td>
                      <td><?php echo (isset($matches[3]))? $matches[3] : ""; ?></td>
                      <td><?php echo $values[0]; ?></td>
                      <td><?php echo $values[1]; ?></td></tr>
                  <?php endforeach; ?>
                </tbody>
            </table>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

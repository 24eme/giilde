<?php if (!$isTeledeclarationMode && $drm->exist('transmission_douane') && $drm->transmission_douane->coherente === false): ?>
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
                    <td colspan="3">Identification du problème rencontré (<a href="<?php echo url_for('drm_retour', $drm); ?>">XML reçu</a>)</td>
                    <td>Valeur proDou@ne</td>
                    <th>Valeur Interpro</th>
                  </tr>
                  <?php foreach ($drm->getXMLComparison()->getFormattedXMLComparaison() as $problemeSrc => $values): ?>
                      <?php preg_match('/^(\[.+\]) (.+)((entree|sortie|stock).+)$/', $problemeSrc, $matches); ?>
                    <tr>
                      <td><?php echo $matches[1]; ?></td>
                      <td><?php echo $matches[2]; ?></td>
                      <td><?php echo $matches[3]; ?></td>
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

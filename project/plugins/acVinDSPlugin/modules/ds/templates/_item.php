<?php
$drm = null;
if (isset($prod_vol))
  $drm = $declaration->getDocument()->getLastDRM();
?>
<tr>
    <td><?php
echo $prod_libelle;
if ($declaration->hasElaboration())
    echo " - en cave";
?>
    </td><td>
        <?php
if ($drm) {
  echo " <span>(<a href=\"" . url_for('drm_visualisation', array('identifiant' => $drm->identifiant, 'periode_version' => $drm->getPeriodeAndversion())) . "\">Vol. $ds_origine : $prod_vol</a>)</span>";
}
        ?></td>
    <td><?php
        echo $form['volumeStock_' . $key]->renderError();
        echo $form['volumeStock_' . $key]->render();
        ?></td>
    <td class="colonne_vci"><?php
        echo $form['vci_' . $key]->renderError();
        echo $form['vci_' . $key]->render();
        ?></td>
    <td class="colonne_reservequalitative"><?php
        echo $form['reserveQualitative_' . $key]->renderError();
        echo $form['reserveQualitative_' . $key]->render();
        ?></td>
</tr>
<?php if ($declaration->hasElaboration()): ?>
    <tr>
        <td><?php echo $prod_libelle; ?> - en élaboration</td>
        <td>&nbsp;</td>
        <td><?php
    echo $form['elaboration_' . $key]->renderError();
    echo $form['elaboration_' . $key]->render();
    ?></td>
        <td class="colonne_vci">&nbsp;</td>
        <td class="colonne_reservequalitative">&nbsp;</td>
    </tr>
<?php endif; ?>

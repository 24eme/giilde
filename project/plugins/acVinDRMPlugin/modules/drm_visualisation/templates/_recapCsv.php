<?php if($csv = CSVDRMClient::getInstance()->findFromIdentifiantPeriode($drm->identifiant, $drm->periode)): ?>
    <h3>Logiciel tiers</h3>
    <p style="margin-bottom: 30px;">Cette drm a été initialisée à partir d'un fichier issu d'une logiciel tiers : <a href="<?php echo url_for('drm_verification_fichier_edi', array('identifiant' => $drm->identifiant, 'periode' => $drm->periode, 'nocheck'=> true, 'md5' => md5(file_get_contents($csv->getAttachmentUri('import_edi_'.$drm->identifiant.'_'.$drm->periode.'.csv'))))) ?>">Voir le fichier</a></p>
<?php endif; ?>

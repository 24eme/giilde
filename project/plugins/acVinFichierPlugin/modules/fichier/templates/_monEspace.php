<?php use_helper('Date'); ?>

<?php if (!$sf_user->isAdmin()): ?>
    <?php return; ?>
<?php endif; ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Documents</h3>
        </div>
            <div class="panel-body">
                <p>Espace de téléversement de document pour le déclarant.</p>
                <div style="margin-top: 50px;">
                	<a class="btn btn-block btn-default" href="<?php echo url_for('upload_fichier', $etablissement) ?>">Ajouter un document</a>
                    <a class="btn btn-xs btn-default btn-block invisible" href="">&nbsp;</a>
                </div>
            </div>
    </div>
</div>
<?php if (class_exists("DRClient") && $etablissement->famille == EtablissementFamilles::FAMILLE_PRODUCTEUR && in_array('drev', sfConfig::get('sf_enabled_modules'))): ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">DR  <?php echo $campagne; ?></h3>
        </div>
            <div class="panel-body">
                <p>Espace de saisie de la Déclaration de Récolte pour le déclarant.</p>
                <div style="margin-top: 50px; margin-bottom: 26px;">
                	<a class="btn btn-block btn-default" href="<?php echo ($dr)? url_for('edit_fichier', $dr) : url_for('new_fichier', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'type' => DRClient::TYPE_MODEL)); ?>"><?php echo ($dr)? ($dr->exist('donnees'))? 'Poursuivre les modifications' : 'Modifier la déclaration' : 'Saisir la déclaration'; ?></a>
                	<?php if(!$dr): ?>
                	<a class="btn btn-xs btn-default btn-block pull-right" href="<?php echo url_for('scrape_fichier', array('sf_subject' => $etablissement, 'campagne' => $campagne, 'type' => DRClient::TYPE_MODEL)) ?>"><span class="glyphicon glyphicon-cloud-download"></span>&nbsp;&nbsp;Importer depuis Prodouane</a>
                	<?php endif; ?>
                </div>
            </div>
    </div>
</div>
<?php endif; ?>

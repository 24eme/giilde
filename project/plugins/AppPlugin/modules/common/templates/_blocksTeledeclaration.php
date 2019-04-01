<div class="<?php if($teledeclaration_drm): ?>col-xs-6 <?php else: ?>col-xs-12 <?php endif; ?>">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <div class="col-xs-2 text-right">
              <span class="icon-contrat" style="font-size: 44px;"></span>
            </div>
            <div class="col-xs-10 text-left">
              <h2>Espace contrat</h2>
            </div>
        </div>
        <div class="panel-body">
            <?php include_partial('vrac/bloc_statuts_contrats', array('societe' => $societe, 'contratsSocietesWithInfos' => $contratsSocietesWithInfos, 'etablissementPrincipal' => $etablissement,'accueil' => true)) ?>
        </div>
        <div class="panel-footer text-center">
            <a class="btn btn-default" href="<?php echo url_for('vrac_societe',array('identifiant' => $etablissementPrincipal->identifiant)); ?>" ><?php echo 'Accéder aux Contrats' ?></a>
            <?php echo getPointAideHtml('vrac','menu_acceder') ?>
        </div>
    </div>
</div>

<?php if($teledeclaration_drm): ?>
<div class="col-xs-6">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
          <div class="row">
            <div class="col-xs-2 text-right" style=" margin-top:8px;">
              <span class="icon-drm" style="font-size: 38px;"></span>
            </div>
            <div class="col-xs-10 text-left">
                <h2>Espace DRM</h2>
            </div>
          </div>
          </div>
        <div class="panel-body">
            <div class="row text-center">
                <div class="col-xs-12" >
                  <?php include_component('drm', 'monEspaceDrm', array('etablissement' => $etablissement, 'campagne' => $campagne, 'isTeledeclarationMode' => true, 'btnAccess' => true, 'accueil_drm' => false)); ?>
                </div>
            </div>
          </div>
          <div class="panel-footer" >
            <div class="row">
                <div class="col-xs-12 text-center">

                    <a class="btn btn-default" href="<?php echo url_for('drm_societe',array('identifiant' => $etablissementPrincipal->identifiant)); ?>" >
                        <?php echo 'Accéder aux DRM' ?>
                    </a>
                    <?php echo getPointAideHtml('drm','menu_acceder') ?>
                </div>
            </div>

        </div>
    </div>
</div>
<?php endif; ?>

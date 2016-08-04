<?php
$hasBlog = false;
?>

<div class="<?php if($teledeclaration_drm && $hasBlog): ?> col-xs-4<?php elseif($teledeclaration_drm || $hasBlog): ?>col-xs-6 <?php else: ?>col-xs-12 <?php endif; ?>">

    <div class="panel panel-default">
        <div class="panel-heading" style="min-height: 85px;">
          <div class="row">
            <div class="col-xs-2 text-right">
              <span class="icon-contrat" style="font-size: 52px;"></span>
            </div>
            <div class="col-xs-10 text-left">
              <h2>Espace contrat</h2>
            </div>
          </div>
        </div>
        <div class="panel-body" style="height: 250px;">
            <div class="row">
                <div class="col-xs-12" >
                  <?php include_partial('vrac/bloc_statuts_contrats', array('societe' => $societe, 'contratsSocietesWithInfos' => $contratsSocietesWithInfos, 'etablissementPrincipal' => $etablissement,'accueil' => true)) ?>

                </div>
            </div>
          </div>
          <div class="panel-footer"   >
            <div class="row">
                <div class="col-xs-12 text-center">


                    <a class="btn btn-default" href="<?php echo url_for('vrac_societe',array('identifiant' => $etablissementPrincipal->identifiant)); ?>" >
                        <?php echo 'Accéder aux Contrat' ?>
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>
<?php if($teledeclaration_drm): ?>
<div class="<?php if($hasBlog): ?> col-xs-4<?php else: ?> col-xs-6<?php endif; ?>">
    <div class="panel panel-default">
        <div class="panel-heading"  style="min-height: 85px;">
          <div class="row">
            <div class="col-xs-2 text-right" style=" margin-top:8px;">
              <span class="icon-drm" style="font-size: 38px;"></span>
            </div>
            <div class="col-xs-10 text-left">
                <h2>Espace DRM</h2>
            </div>
          </div>
          </div>
        <div class="panel-body" style="height: 250px;">
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

                </div>
            </div>

        </div>
    </div>
</div>
<?php endif; ?>
<?php if($hasBlog): ?>
<div class="<?php if($teledeclaration_drm): ?> col-xs-4<?php else: ?>col-xs-6<?php endif; ?>">
<div class="panel panel-default">
    <div class="panel-heading"  style="min-height: 85px;"><h2>Espace Blog</h2></div>
    <div class="panel-body" style="height: 250px;">
        <div class="row">
            <div class="col-xs-12" style="height: 150px;">

            </div>
        </div>
      </div>
      <div class="panel-footer"  >
        <div class="row">
            <div class="col-xs-12 text-center">


                <a class="btn btn-default" href="#" >
                    <?php echo 'Accéder au blog' ?>
                </a>

            </div>
        </div>

    </div>
</div>
</div>
<?php endif; ?>

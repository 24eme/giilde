<?php
use_helper("DRMXml");
?>

    <?php include_partial('drm/header', array('drm' => $drm)); ?>
    <h2>Visualisation de l'XML <?php if($retour) : ?>reçu Douane <?php else: ?>transmis <?php endif; ?><a class="btn_majeur" href="<?php if($retour) : echo url_for('drm_retour', $drm); else: echo url_for('drm_xml', $drm); endif; ?>"><small>(fichier XML brut)</small></a>
        <a class="pull-right btn btn-sm btn-default" href="<?php echo url_for('drm_xml_table', array('identifiant' => $drm->identifiant,"periode_version" => $drm->getPeriodeAndVersion(), 'retour' => intval(!$retour))); ?>"><span class="glyphicon glyphicon-eye-open"></span>&nbsp;<?php if(!$retour) : ?> XML reçu Douane <?php else: ?> XML transmis <?php endif; ?></a>
    </h2>

    <?php if(!count($xml_table)): ?>
        <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th colspan="8"><h4>Absense de l'XML</h4></th>
            </tr>
        </thead>
    </table>
    <?php else: ?>
      <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th colspan="8"><h4>Description général</h4></th>
            </tr>
        </thead>
      <tbody>
            <?php echo xmlPartOfToTable($xml_table,array("identification-declarant","periode","declaration-neant")); ?>
      </tbody>
    </table>
    <br/>

    <table class="table table-striped table-condensed">
        <thead>
            <tr>
                <th colspan="8"><h4>droits-suspendus</h4></th>
            </tr>
        </thead>
        <tbody>
              <?php echo xmlProduitsToTable($xml_table,"droits-suspendus"); ?>
        </tbody>
  </table>
  <br/>

  <table class="table table-striped table-condensed">
      <thead>
          <tr>
              <th colspan="8"><h4>droits-acquittes</h4></th>
          </tr>
      </thead>
      <tbody>
            <?php echo xmlProduitsToTable($xml_table,"droits-acquittes"); ?>
      </tbody>
</table>

<br/>

<table class="table table-striped table-condensed">
    <thead>
        <tr>
            <th colspan="8"><h4>compte-crd</h4></th>
        </tr>
    </thead>
    <tbody>
          <?php echo xmlCrdsToTable($xml_table,"compte-crd"); ?>
    </tbody>
</table>
<?php endif; ?>

<div class="row">
    <div class="col-xs-12 text-left">
    <a href="<?php echo url_for('drm_visualisation', $drm); ?>" class="btn btn-default"><span>Retour à la visualisation de la DRM</span></a>
    </div>
</div>


<?php
include_partial('drm/colonne_droite', array('drm' => $drm, 'isTeledeclarationMode' => false));
?>

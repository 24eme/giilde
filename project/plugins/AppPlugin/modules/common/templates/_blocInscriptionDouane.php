<?php $interpro = sfConfig::get('app_teledeclaration_interpro'); ?>


<div class="well">
    <p>Vous pouvez dès à présent déclarer votre DRM sur ce portail ce qui vous permettra de récupérer le document PDF à envoyer aux douanes.</p>
</div>

<div id="convention_ciel" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Adhésion à l'application CIEL du portail des douanes</h4>
      </div>
      <div class="modal-body">
        <p>Pour que nous puissions transmette vos DRM à la douane par l'intermédiaire de l'application CIEL du portail Prodouane, il est indispensable de préalablement remplir, signer et déposer la <a target="_blank" href="/pdf/adhesion_ciel_prodouane_<?php echo $interpro;?>.pdf">convention d'adhésion à CIEL</a> auprès des douanes.

        <p>Les douanes nous retourneront ensuite le volet qui nous est destiné comme preuve de dépôt : un délai de quelques jours est donc nécessaire pour activer la connexion.</p>

        <p>En l'absence de cette convention, toute DRM télédéclarée sur notre portail devra faire l’objet d’une impression (proposée en fin de saisie) et être déposée physiquement aux douanes sous format papier pour remplir vos obligations règlementaires.</p>

        <p>Au cas où vous auriez déposé cette convention d'adhésion auprès des douanes très récemment, veuillez prendre contact avec l’<?php echo strtoupper($interpro);?> (<?php echo strtoupper(sfConfig::get('app_teledeclaration_numero_interpro_adhesion'));?>) préalablement à la saisie de votre DRM.</p>
      </div>
      <div class="modal-footer text-center">
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Fermer</button>
          <a target="_blank" class="btn btn-info" href="/pdf/adhesion_ciel_prodouane_<?php echo $interpro;?>.pdf">Télécharger la convention d'adhésion à CIEL</a>
      </div>
    </div>
  </div>
</div>

<?php
use_helper('Vrac');
use_helper('Float');
use_helper('PointsAides');
?>
<ol class="breadcrumb">
    <li><a href="<?php echo url_for('vrac_societe', array('identifiant' => $etablissementPrincipal->identifiant)); ?>" class="active">Contrats</a></li>
</ol>

<section id="principal" class="vrac">
  <div class="row">
    <div class="col-xs-1 text-right" style="padding-left:10px;">
      <span class="icon-contrat" style="font-size: 70px;"></span>
    </div>
    <div class="col-xs-11">
    <h2 class="titre_societe titre_societe_teledeclaration">
        Espace contrats de <?php echo $societe->raison_sociale; ?>
    </h2>
  </div>
</div>
    <br/>
        <div class="row">
            <?php
            $panel_size = ' col-xs-4 ';
            if (!$societe->isNegociant()):
                $panel_size = ' col-xs-6 ';
              endif;
            if ($societe->isNegociant() || $societe->isCourtier()):
                ?>
                <div class="<?php echo $panel_size; ?>" >
                <div class="panel panel-default">
                    <div class="panel-heading <?php echo ($contratsSocietesWithInfos->infos->brouillon) ? "actif" : ""; ?>">Brouillon<div class="pull-right"><?php echo getPointAideHtml('vrac','menu_list_brouillon'); ?></div></div>
                        <div class="panel-body">
                        <?php if ($contratsSocietesWithInfos->infos->brouillon): ?>
                            <a href="<?php echo url_for('vrac_history', array('identifiant' => $etablissementPrincipal->identifiant, 'campagne' => ConfigurationClient::getInstance()->getCurrentCampagne(), 'etablissement' => 'tous', 'statut' => strtolower(VracClient::STATUS_CONTRAT_BROUILLON))) ?>">
                            <?php endif; ?>
                            <?php echo $contratsSocietesWithInfos->infos->brouillon; ?> contrat(s) en brouillon<?php echo getPointAideHtml('vrac','menu_list_acces_brouillon'); ?>
                            <?php if ($contratsSocietesWithInfos->infos->brouillon): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
            <?php endif; ?>
            <?php if ($societe->isNegociant() || $societe->isViticulteur()): ?>
            <div class="<?php echo $panel_size; ?>" >
                <div class="panel panel-default">
                    <div class="panel-heading  <?php echo ($contratsSocietesWithInfos->infos->a_signer) ? "actif" : ""; ?>">A Signer<div class="pull-right"><?php echo getPointAideHtml('vrac','menu_list_asigner'); ?></div></div>
                        <div class="panel-body">
                        <?php if ($contratsSocietesWithInfos->infos->a_signer): ?>
                            <a href="<?php echo url_for('vrac_history', array('identifiant' => $etablissementPrincipal->identifiant, 'campagne' => ConfigurationClient::getInstance()->getCurrentCampagne(), 'etablissement' => 'tous', 'statut' => strtolower(VracClient::STATUS_SOUSSIGNECONTRAT_ATTENTE_SIGNATURE_MOI))) ?>">
                            <?php endif; ?>
                            <?php echo $contratsSocietesWithInfos->infos->a_signer; ?> contrat(s) à signer<?php echo getPointAideHtml('vrac','menu_list_acces_asigner'); ?>
                            <?php if ($contratsSocietesWithInfos->infos->a_signer): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="<?php echo $panel_size; ?>" >
            <div class="panel panel-default">
                <div class="panel-heading  <?php echo ($contratsSocietesWithInfos->infos->en_attente) ? "actif" : ""; ?>">En Attente<div class="pull-right"><?php echo getPointAideHtml('vrac','menu_list_enattente'); ?></div></div>
                    <div class="panel-body">
                    <?php if ($contratsSocietesWithInfos->infos->en_attente): ?>
                        <a href="<?php echo url_for('vrac_history', array('identifiant' => $etablissementPrincipal->identifiant, 'campagne' => ConfigurationClient::getInstance()->getCurrentCampagne(), 'etablissement' => 'tous', 'statut' => strtolower(VracClient::STATUS_SOUSSIGNECONTRAT_ATTENTE_SIGNATURE_AUTRES))) ?>">
                        <?php endif; ?>
                        <?php echo $contratsSocietesWithInfos->infos->en_attente; ?> contrat(s) en attente<?php echo getPointAideHtml('vrac','menu_list_acces_enattente'); ?>
                        <?php if ($contratsSocietesWithInfos->infos->en_attente): ?>
                        </a>
                    <?php endif; ?>
                </div>
                  </div>
            </div>
        </div>

    <div class="row">
      <div class="col-xs-12">
        <a class="btn btn-default" href="<?php echo url_for('vrac_history', array('identifiant' => $etablissementPrincipal->identifiant, 'campagne' => ConfigurationClient::getInstance()->getCurrentCampagne(), 'etablissement' => 'tous')); ?>">
            Voir tout l'historique
        </a>
        <?php if ($societe->isCourtier() || $societe->isNegociant()):
          if ($societe->isNegociant()) {
            $etablissementCreateur = $societe->getNegociant();
          }else{
            $etablissementCreateur = $etablissementPrincipal;
          }
          ?>
            <div class="pull-right">
              <div class="btn-group">
                <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Saisir un nouveau contrat <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li><a href="<?= url_for('vrac_nouveau', ['choix-etablissement' => $etablissementCreateur->identifiant]); ?>">Manuellement</a></li>
                  <li><a href="<?= url_for('vrac_upload_index') ?>">Via un fichier</a></li>
                </ul>
              </div>
              <?php echo getPointAideHtml('vrac','menu_acces_nouveau'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<br/>
    <?php include_partial('vrac/list', array('vracs' => $contratsSocietesWithInfos, 'teledeclaration' => true,'societe' => $societe)); ?>




</section>


<?php
//include_partial('colonne_droite', array('societe' => $societe, 'etablissementPrincipal' => $etablissementPrincipal));
?>

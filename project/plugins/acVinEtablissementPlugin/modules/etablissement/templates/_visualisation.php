<?php use_helper('Etablissement'); ?>

<div class="list-group" id="<?php echo $etablissement->_id; ?>">
    <div class="list-group-item<?php echo ($etablissement->isSuspendu()) ? ' disabled': '' ?>">
        <div class="row">
            <h2 style="margin-top: 5px; margin-bottom: 5px;" class="col-xs-10"><span class="<?php echo comptePictoCssClass($etablissement->getRawValue()) ?>"></span> <?php echo $etablissement->nom; ?>
                <small class="text-muted">(n° de chai : <?php echo $etablissement->identifiant; ?>)</small>
            </h2>
            <div class="col-xs-2 text-right">
                      <div class="btn-group">
                        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">Modifier <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                         <li<?php echo ($etablissement->getSociete()->isSuspendu() || $etablissement->isSuspendu()) ? ' class="disabled"' : ''; ?>><a href="<?php echo ($etablissement->getSociete()->isSuspendu() || $etablissement->isSuspendu()) ? 'javascript:void(0)' : url_for('etablissement_modification', $etablissement); ?>">Editer</a></li>
                         <li<?php echo ($etablissement->getSociete()->isSuspendu() || $etablissement->isSuspendu())? ' class="disabled"' : ''; ?>><a href="<?php echo ($etablissement->getSociete()->isSuspendu() || $etablissement->isSuspendu())? 'javascript:void(0)' : url_for('etablissement_switch_statut', array('identifiant' => $etablissement->identifiant)); ?>">Suspendre</a></li>
                         <li<?php echo ($etablissement->getSociete()->isSuspendu() || $etablissement->isActif())? ' class="disabled"' : ''; ?>><a href="<?php echo ($etablissement->getSociete()->isSuspendu() || $etablissement->isActif())? 'javascript:void(0)' : url_for('etablissement_switch_statut', array('identifiant' => $etablissement->identifiant)); ?>">Activer</a></li>
                         <li<?php echo ($etablissement->getSociete()->isSuspendu() || $etablissement->isSuspendu() || !$etablissement->exist('crd_regime') || !$etablissement->crd_regime)? ' class="disabled"' : ''; ?>><a href="<?php echo ($etablissement->getSociete()->isSuspendu() || $etablissement->isSuspendu() || !$etablissement->exist('crd_regime') || !$etablissement->crd_regime)? 'javascript:void(0)' : url_for('etablissement_reinit_crd', array('identifiant' => $etablissement->identifiant)); ?>">Réinitialiser régime CRD</a></li>
                       </ul>
            </div></div>
        </div>
        <div class="row">
            <div class="col-xs-9">
                <p class="lead" style="margin-bottom: 5px;">
                    <span class="label label-primary"><?php echo EtablissementFamilles::getFamilleLibelle($etablissement->famille); ?></span>
                    <?php if ($etablissement->getNatureLibelle()): ?>
                        <span class="label label-info"><?php echo $etablissement->getNatureLibelle(); ?></span>
                    <?php endif; ?>
                    <?php if ($etablissement->isSuspendu()): ?>
                        <span class="label label-default"><?php echo $etablissement->statut; ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    <?php if ($etablissement->isSameAdresseThanSociete()): ?>
        <div class="list-group-item text-center text-muted<?php echo ($etablissement->isSuspendu()) ? ' disabled': '' ?>">
            <div class="row">
                <em>Même adresse que la société</em>
            </div>
        </div>
    <?php else : ?>
        <div class="list-group-item text-center<?php echo ($etablissement->isSuspendu()) ? ' disabled': '' ?>">
            <div class="row">
                <?php include_partial('compte/adresseVisualisation', array('compte' => $etablissement->getMasterCompte(), 'modification' => $modification, 'reduct_rights' => $reduct_rights, 'smallBlock' => false)); ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($etablissement->isSameContactThanSociete()): ?>
        <div class="list-group-item text-center text-muted<?php echo ($etablissement->isSuspendu()) ? ' disabled': '' ?>">
            <div class="row">
                <em>Même contact que la société</em>
            </div>
        </div>
    <?php else : ?>
        <div class="list-group-item text-center<?php echo ($etablissement->isSuspendu()) ? ' disabled': '' ?>">
            <div class="row">
                <?php include_partial('compte/contactVisualisation', array('compte' => $etablissement->getMasterCompte(), 'modification' => $modification, 'reduct_rights' => $reduct_rights, 'smallBlock' => true)); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($etablissement->exist('mois_stock_debut')): ?>
      <div class="list-group-item text-center">
        <div class="row">
          <div class="col-xs-12  text-left">
              <div class="row">
                  <div class="col-xs-3">
                      <strong>Mois de saisie du stock :</strong>
                  </div>
                  <div class="col-xs-9">
                      <strong><?php echo ucfirst(format_date(date("Y")."-".$etablissement->mois_stock_debut."-01", 'MMMM', 'fr_FR')); ?></strong>
                  </div>
              </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="list-group-item text-muted<?php echo ($etablissement->isSuspendu()) ? ' disabled': '' ?>">
        <?php if ($etablissement->isSameCompteThanSociete()): ?>
            <div class="row">
                <div class="col-xs-12 text-center">
                    <em>Même tags que la société</em>
                </div>
            </div>
        <?php else: ?>
            <?php include_partial('compte/tagsVisualisation', array('compte' => $etablissement->getMasterCompte(), 'modification' => $modification, 'reduct_rights' => $reduct_rights, 'smallBlock' => true)); ?>
        <?php endif; ?>
    </div>
    <div class="list-group-item<?php echo ($etablissement->isSuspendu()) ? ' disabled': '' ?>">
        <ul class="list-inline">
            <?php if ($etablissement->recette_locale && $etablissement->recette_locale->nom) : ?>
                <li><attr>Recette locale :</attr> <a href="<?php echo url_for('societe_visualisation', SocieteClient::getInstance()->find($etablissement->recette_locale->id_douane)); ?>">
                    <?php echo $etablissement->recette_locale->nom; ?></a></li>
            <?php endif; ?>
            <?php if ($etablissement->cvi): ?>
                <li>CVI : <?php echo $etablissement->cvi; ?></li>
            <?php endif; ?>
            <?php if ($etablissement->no_accises): ?>
                <li>Numéro d'accise : <?php echo $etablissement->no_accises; ?></li>
            <?php endif; ?>
            <?php if ($etablissement->carte_pro && $etablissement->isCourtier()) : ?>
                <li>Carte professionnelle : <?php echo $etablissement->carte_pro; ?></li>
            <?php endif; ?>
            <li>Région : <?php echo $etablissement->region; ?></li>
            <?php if ($etablissement->exist('crd_regime') && $etablissement->getCrdRegimeArray()) : ?>
                <li>Régime CRD :&nbsp;
                   <?php foreach ($etablissement->getCrdRegimeArray() as $crd_regime):
                       echo EtablissementClient::$regimes_crds_libelles[$crd_regime].'&nbsp;&nbsp;';
                    ?>
                   <br/>
               <?php endforeach ?>
               </li>
           <?php endif; ?>
        </ul>

        <?php if ($etablissement->commentaire) : ?>
            <strong>Commentaires :</strong> <?php echo $etablissement->commentaire; ?>
        <?php endif; ?>
    </div>
</div>


<?php
$typesLiaisons = EtablissementClient::getTypesLiaisons();
if (!isset($fromSociete))
    $fromSociete = false;
?>

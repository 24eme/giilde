<?php
use_helper('Vrac');
use_helper('PointsAides');
?>
<section id="principal">
  <ol class="breadcrumb">
      <li><a href="<?php echo url_for('vrac_societe', array('identifiant' => $etablissementPrincipal->identifiant)); ?>" class="active">Contrats</a><?php echo getPointAideHtml('vrac','annuaire_fil_saisi_retour_liste_contrat'); ?></li>
      <li><a href="<?php echo url_for('annuaire', array('identifiant' => $etablissementPrincipal->identifiant)); ?>" class="active">Annuaire</a><?php echo getPointAideHtml('vrac','annuaire_fil_saisi_retour_annuaire'); ?></li>
  </ol>

  <h2>Ajouter un contact</h2>
        <form id="principal" method="post" action="<?php echo url_for('annuaire_ajouter', array('identifiant' => $identifiant, 'type' => $type, 'tiers' => $societeId)) ?>">

                <?php echo $form->renderHiddenFields() ?>
                <?php echo $form->renderGlobalErrors() ?>

                <div class="row">
                      <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><stong>Saisissez ici le type et l'identifiant du tiers que vous souhaitez ajouter à votre annuaire</strong></div>
                      <div class="panel-panel">
                  <div class="row">
                      <div class="col-xs-12">
                              <ul class="list-group" style="margin-bottom:0px;">
                              <li class="list-group-item" >
                                      <div class="row">

                                        <div class="col-xs-4">Type<?php echo getPointAideHtml('vrac','annuaire_selection_type'); ?></div>
                            						<div class="col-xs-8">Identifiant<?php echo getPointAideHtml('vrac','annuaire_selection_numero'); ?></div>
                    </div>
                  </li>
                  <li class="list-group-item" >
                  <div class="row">
                    <div class="col-xs-4"><?php if(isset($form['type'])): ?>
                      <span><?php echo $form['type']->renderError() ?></span>
                        <?php echo $form['type']->render() ?>
                      <?php else: ?>
                        Viticulteur
                      <?php endif; ?>
                    </div>
                    <div class="col-xs-8">
                      <span><?php echo $form['tiers']->renderError() ?></span>
                      <?php echo $form['tiers']->render() ?>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
            </div>
        </div>
                      </div>
                      </div>
                      </div>

                <br />
                <div class="row">
                      <div class="col-xs-12">

                <?php if (!$form->hasSocieteChoice()): ?>
                    <h2>
                      <div class="row">
                        <div class="col-xs-3">  INFORMATIONS</div>
                        <div class="col-xs-5 pull-left" style="font-size:12pt; padding-top:12px;"><?php echo getPointAideHtml('vrac','annuaire_verification_infos'); ?></div>
                      </div>
                    </h2>
                  </div>
                      </div>
  <div class="row">
                      <div class="col-xs-12">
                    <div class="well">
                                    <ul class="list-unstyled">
                                        <li>Nom : <strong><?php echo $etbObject->nom ?></strong></li>
                                        <li>N° CVI : <strong><?php echo $etbObject->cvi ?></strong></li>
                                        <li>N° d'ACCISE : <strong><?php echo $etbObject->no_accises ?></strong></li>
                                        <li>Téléphone : <strong><?php echo $etbObject->telephone ?></strong></li>
                                        <li>Fax : <strong><?php echo $etbObject->fax ?></strong></li>
                                        <li>Adresse : <strong><?php echo $etbObject->siege->adresse ?></strong></li>
                                        <li>Code postal : <strong><?php echo $etbObject->siege->code_postal ?></strong></li>
                                        <li>Commune : <strong><?php echo $etbObject->siege->commune ?></strong></li>
                                    </ul>
                    </div>
                  </div>
                  </div>
                <?php endif; ?>
                <?php if ($form->hasSocieteChoice()): ?>
                    <h2>Choix d'un établissement</h2>
                    <p>Choisissez l'établissement de la société à ajouter à votre annuaire :</p><br />
                    <span><?php echo $form['etablissementChoice']->renderError() ?></span>

                    <div class="bloc_form bloc_form_condensed">
                    <?php
                    $cpt = 0;
                    foreach ($etablissements as $etablissement) :
                        $etb = $etablissement->etablissement;
                        $selected = (!$cpt)? 'checked="checked"' : ''; ?>

                        <div  class="<?php echoClassLignesVisu($cpt);?>">
                                        <input id="annuaire_ajout_etablissementChoice_<?php echo $etb->identifiant; ?>"
                                               type="radio"
                                               value="<?php echo $etb->identifiant; ?>" name="annuaire_ajout[etablissementChoice]"
                                               <?php echo $selected ?> >
                                        <label for="annuaire_ajout_etablissementChoice_<?php echo $etb->identifiant; ?>">
                                        <?php
                                        $nomCvi = $etb->nom;
                                        $nomCvi .= ($etb->cvi)? ' ('.$etb->cvi.')' : '';
                                        echo $nomCvi;
                                        ?>
                                        </label>
                        </div>
                    <?php
                    endforeach;
                    ?>
                    </div>
                <?php endif; ?>

            <div class="row">
              <div class="col-xs-12">
                <a class="btn btn-default" href="<?php echo url_for('annuaire_selectionner', array('identifiant' => $identifiant, 'type' => $type)) ?>">Retour</a>
                <?php echo getPointAideHtml('vrac','annuaire_fil_saisi_retour_contrat'); ?>
                <button type="submit" name="valider" class="btn btn-success pull-right" >
                    Valider
                </button>
            </div>
            </div>
  </form>
    <!-- <?php include_partial('vrac/popup_notices'); ?> -->
</div>

<?php
// include_partial('vrac/colonne_droite', array('societe' => $societe, 'etablissementPrincipal' => $etablissementPrincipal));
?>

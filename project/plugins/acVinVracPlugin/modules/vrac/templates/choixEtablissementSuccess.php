<?php
?>
<section id="principal">



    <form id="vrac_choix_etablissement" method="post" action="<?php echo url_for('vrac_societe_choix_etablissement', array('identifiant' => $societe->identifiant)) ?>">
        <h2 class="titre_societe">
            Espace de <?php echo $societe->raison_sociale; ?>
        </h2>

        <p class="titre_section">Votre société possède plusieurs établissements. Veuillez renseigner l'établissement en charge de ce contrat.</p>
        <br/>
        <div class="row">
         <div id="condition" class="fond col-sm-12" >
             <?php echo $form->renderHiddenFields() ?>
             <?php echo $form->renderGlobalErrors() ?>

                <div id="type_contrat" class="ligne_form">
                    <?php echo $form['etablissementChoice']->renderError() ?>
                    <?php echo $form['etablissementChoice']->renderLabel() ?>
                    <?php echo $form['etablissementChoice']->render() ?>
                </div>
                <br/><br/>
          </div>
          <div class="col-sm-12">
                <div class="col-sm-1">
                  <a class="btn btn-default" href="<?php echo url_for('vrac_societe', array('identifiant' => $etablissementPrincipal->identifiant)); ?>">Retourner à l'espace contrats</a>
                </div>
                <div class="col-sm-offset-9 col-sm-1">
                  <button class="btn btn-primary" style="cursor: pointer;" type="submit"><span>Nouveau Contrat</span></button>
                </div>
            </div>
         </div>
    </form>
    <?php include_partial('popup_notices'); ?>
</section>

<?php

include_partial('colonne_droite', array('societe' => $societe, 'etablissementPrincipal' => $etablissementPrincipal));

?>

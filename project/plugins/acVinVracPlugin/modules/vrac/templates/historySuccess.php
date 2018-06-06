<?php
use_helper('Vrac');
use_helper('Float');
?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('vrac_societe', array('identifiant' => $etablissementPrincipal->identifiant)); ?>" class="active">Contrats</a></li>
    <li><a href="#" class="active">Historique</a></li>
</ol>

<section id="principal">
    <h2 class="titre_societe">
        Historique contrat de <?php echo $societe->raison_sociale; ?>
    </h2>
    <br/>
    <div class="row">

      <div class="col-xs-12">
        <form class="filtres_historique" id="filtres_historique" action="<?php echo url_for('vrac_history',array('identifiant' => $identifiant)); ?>" method="POST">
        <?php echo $form->renderHiddenFields();
		          echo $form->renderGlobalErrors(); ?>

  <div class="row">
    <?php $col_size = (!$isOnlyOneEtb)? 'col-xs-4' : 'col-xs-6'; ?>
          <div class="<?php echo $col_size; ?> campagne">
              <?php echo $form['campagne']->renderError(); ?>
  			<?php echo $form['campagne']->renderLabel() ?>
  			<?php echo $form['campagne']->render(array('class' => 'select2 form-control')) ?>
          </div>
         <?php if(!$isOnlyOneEtb): ?>
              <div class="<?php echo $col_size; ?> etablissement">
                              <?php echo $form['etablissement']->renderError(); ?>
                              <?php echo $form['etablissement']->renderLabel() ?>
                              <?php echo $form['etablissement']->render(array('class' => 'select2 form-control')) ?>
              </div>
         <?php endif; ?>
          <div class="<?php echo $col_size; ?> statut">
  			<?php echo $form['statut']->renderError(); ?>
  			<?php echo $form['statut']->renderLabel() ?>
  			<?php echo $form['statut']->render(array('class' => 'select2 form-control')) ?>
          </div>
      </form>
  </div>
</div>
</div>
<br/>
        <div class="row">
          <div class="col-xs-12">
              <input type="hidden" data-placeholder="Saisissez un produit, un numéro de contrat ou un nom de soussigné :" data-hamzastyle-container="#table_contrats" class="hamzastyle" />
         </div>
       </div>
       <br/>
<div class="row">
<div class="col-xs-12">

    <a class="btn btn-default" href="<?php echo url_for('vrac_history_exportCsv', array('identifiant' => $etablissementPrincipal->identifiant, 'campagne' => $campagne, 'etablissement' => $etablissement, 'statut' => $statut)); ?>">
        <span class="glyphicon glyphicon-save"></span> Exporter en Tableur
    </a>
</div>
</div>
<br/>
       <?php include_partial('vrac/list', array('vracs' => $contratsByCampagneEtablissementAndStatut, 'teledeclaration' => true,'societe' => $societe)); ?>

</section>

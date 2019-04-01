<ol class="breadcrumb">
    <li><a href="<?php echo url_for('statistiques') ?>">Statistique</a></li>
    <li><a href="<?php echo url_for('statistiques_stats') ?>" class="active">Statistiques</a></li>
</ol>
<div class="row" id="statistiques">
    <div class="col-xs-12">
	    <form class="popup_form form_delay form-horizontal" id="filtres_stat" action="<?php echo url_for('statistiques_stats') ?>" method="post">
			<?php echo $form->renderGlobalErrors() ?>
			<?php echo $form->renderHiddenFields() ?>

			<div id="stat_choices" class="form-group bloc_condition" data-condition-cible="#bloc_appellation|#bloc_famille|#bloc_periode|#bloc_lastyear|#bloc_produit|#bloc_date|#bloc_conditionnement">
				<span class="error has-error"><?php echo $form['statistiques']->renderError() ?></span>
		        <div class="col-xs-12" style="text-align:center;"><?php echo $form['statistiques']->render(); ?></div>
			</div>

			<div id="bloc_appellation" class="form-group bloc_conditionner" data-condition-value="exportations|stocks|disponibilites_stocks">
        <?php if(isset($form['doc.mouvements.appellation'])): ?>
          <span class="error has-error"><?php echo $form['doc.mouvements.appellation']->renderError() ?></span>
             <?php echo $form['doc.mouvements.appellation']->renderLabel(null, array("class" => "col-xs-6 control-label")); ?>
             <div class="col-xs-6"><?php echo $form['doc.mouvements.appellation']->render(); ?></div>
        <?php endif; ?>
        <?php if(isset($form['doc.mouvements.produit_hash'])): ?>
             <span class="error has-error"><?php echo $form['doc.mouvements.produit_hash']->renderError() ?></span>
             <?php echo $form['doc.mouvements.produit_hash']->renderLabel(null, array("class" => "col-xs-6 control-label")); ?>
             <div class="col-xs-6"><?php echo $form['doc.mouvements.produit_hash']->render(); ?></div>
           <?php endif; ?>
			</div>
      <div id="bloc_produit" class="form-group bloc_conditionner" data-condition-value="prix|mentions_valorisantes|disponibilites_vracs">
        <?php if(isset($form['doc.appellation'])): ?>
          <span class="error has-error"><?php echo $form['doc.appellation']->renderError() ?></span>
          <?php echo $form['doc.appellation']->renderLabel(null, array("class" => "col-xs-6 control-label")); ?>
          <div class="col-xs-6"><?php echo $form['doc.appellation']->render(); ?></div>
        <?php endif; ?>
        <?php if(isset($form['doc.produit'])): ?>
          <span class="error has-error"><?php echo $form['doc.produit']->renderError() ?></span>
          <?php echo $form['doc.produit']->renderLabel(null, array("class" => "col-xs-6 control-label")); ?>
          <div class="col-xs-6"><?php echo $form['doc.produit']->render(); ?></div>
        <?php endif; ?>
      </div>
      <?php if(StatistiqueConfiguration::getInstance()->isAggregatAppellation()): ?>
			<div id="bloc_famille" class="form-group bloc_conditionner" data-condition-value="sorties_categorie">
				<span class="error has-error"><?php echo $form['doc.declarant.famille']->renderError() ?></span>
		        <?php echo $form['doc.declarant.famille']->renderLabel(null, array("class" => "col-xs-6 control-label")); ?>
		        <div class="col-xs-6"><?php echo $form['doc.declarant.famille']->render(); ?></div>
			</div>
    <?php endif; ?>
			<div id="bloc_conditionnement" class="form-group bloc_conditionner" data-condition-value="prix|mentions_valorisantes">
				<span class="error has-error"><?php echo $form['doc.type_transaction']->renderError() ?></span>
		        <?php echo $form['doc.type_transaction']->renderLabel(null, array("class" => "col-xs-6 control-label")); ?>
		        <div class="col-xs-6"><?php echo $form['doc.type_transaction']->render(); ?></div>
			</div>
			<div id="bloc_periode" class="form-group bloc_conditionner" data-condition-value="exportations|disponibilites_stocks|sorties_categorie|sorties_appellation|stocks">
				<span class="error has-error"><?php echo $form['doc.mouvements.date/from']->renderError() ?></span>
				<span class="error has-error"><?php echo $form['doc.mouvements.date/to']->renderError() ?></span>
				<label class="col-xs-6 control-label">Période</label>
		        <div class="col-xs-3"><?php echo $form['doc.mouvements.date/from']->render(); ?></div>
		        <div class="col-xs-3"><?php echo $form['doc.mouvements.date/to']->render(); ?></div>
			</div>

			<div id="bloc_date" class="form-group bloc_conditionner" data-condition-value="prix|mentions_valorisantes|disponibilites_vracs">
				<span class="error has-error"><?php echo $form['doc.date_campagne/from']->renderError() ?></span>
				<span class="error has-error"><?php echo $form['doc.date_campagne/to']->renderError() ?></span>
				<label class="col-xs-6 control-label">Période</label>
		        <div class="col-xs-3"><?php echo $form['doc.date_campagne/from']->render(); ?></div>
		        <div class="col-xs-3"><?php echo $form['doc.date_campagne/to']->render(); ?></div>
			</div>

			<div id="bloc_lastyear" class="form-group bloc_conditionner" data-condition-value="exportations|sorties_categorie|sorties_appellation<?php echo (StatistiqueConfiguration::getInstance()->isAggregatAppellation())? "" : "|stocks"; ?>">
				<span class="error has-error"><?php echo $form['lastyear']->renderError() ?></span>
		        <?php echo $form['lastyear']->renderLabel(null, array("class" => "col-xs-6 control-label")); ?>
		        <div class="col-xs-6"><?php echo $form['lastyear']->render(); ?></div>
			</div>

			<div class="form_ligne_btn" style="margin-top:20px;">
				<input id="resetBtn" type="reset" value="reset">
				<div class="col-xs-6"><a class="btn btn-default" href="<?php echo url_for('statistiques_stats') ?>">Annuler</a></div>
				<div class="col-xs-6 text-right">
					<span class="error has-error"><?php echo $form['pdf']->renderError() ?></span>
				    <?php echo $form['pdf']->render(); ?>
				    <?php echo $form['pdf']->renderLabel(null, array("class" => "control-label")); ?>
					<button name="valider" class="btn btn-success" type="submit">Exporter</button>
				</div>
			</div>
		</form>

    	<hr />


    </div>
</div>

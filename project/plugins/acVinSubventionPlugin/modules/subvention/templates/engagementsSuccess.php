<?php include_partial('subvention/breadcrumb', array('subvention' => $subvention, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<section id="principal">
    <?php include_partial('subvention/etapes', array('subvention' => $subvention)); ?>

    <form class="form-horizontal" method="POST" action="">
        <?php echo $form->renderGlobalErrors(); ?>
        <?php echo $form->renderHiddenFields(); ?>

        <h2>Engagements du bénéficiaire</h2>

        <p>Merci de vérifier les points suivants et vous engager à les respecter :</p>

        <div class="row">
        	<div class="col-xs-9">
                <?php if($form->hasErrors()): ?>
                    <p style="margin-bottom: 10px; margin-top: 10px;" class="alert alert-danger">Vous devez cocher tous les engagements</p>
                <?php endif; ?>

            	<?php
                $first = true;
                foreach ($form->getEngagements() as $key => $libelle):
                   if (!isset($form["engagement_$key"])) continue;
                ?>
                <?php if(!$first): ?>
                <hr style="margin-bottom: 0; margin-top: 10px;" />
                <?php endif; ?>
                <?php $first = false; ?>
                <div class="form-group <?php if($form["engagement_$key"]->hasError()): ?>has-error<?php endif; ?>" style="margin-bottom:0;">
    				<div class="col-xs-12 checkbox">
    					<label for="<?php echo $form["engagement_$key"]->renderId() ?>">
        				<?php echo $form["engagement_$key"]->render() ?>&nbsp;<?php echo $libelle ?>
        				</label>
                  	</div>
              	</div>
              	<?php endforeach; ?>
    		</div>
            <div class="col-xs-3">
                <?php include_partial('subvention/aide'); ?>
            </div>
        </div>

        <div class="row" style="margin-top: 30px;">
            <div class="col-xs-6">
                <a class="btn btn-default" tabindex="-1" href="<?php echo url_for('subvention_dossier', $subvention); ?>"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Étape précédente</a>
            </div>
            <div class="col-xs-6 text-right">
                <button type="submit" class="btn btn-success">Étape suivante&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
        </div>
    </form>
</section>
<script type="text/javascript">
$(document).ready(function() {
	$("input[type='checkbox']").change(function () {
		var checkbox = $(this);
		if (checkbox.data("target")) {
			if (checkbox.is(':checked')) {
				$(checkbox.data("target")).attr("checked", true);
			}
		} else {
			if (!checkbox.is(':checked')) {
				$("input[data-target='#"+checkbox.attr("id")+"']").attr("checked", false);
			}
		}
	});
});
</script>

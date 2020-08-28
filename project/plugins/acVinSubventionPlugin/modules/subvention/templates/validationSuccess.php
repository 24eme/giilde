<?php include_partial('subvention/breadcrumb', array('subvention' => $subvention, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<section id="principal">
    <?php include_partial('subvention/etapes', array('subvention' => $subvention)); ?>

    <h2>Validation du dossier de préqualification auprès de votre interprofession</h2>

    <p>A cette étape, vérifiez vos informations avant de soumettre votre dossier à la pré-validation de l'interprofession.</p>

    <p>NOTA : si votre projet concerne plusieurs interprofessions, nous communiquerons les éléments que vous avez déposés à ces autres interprofessions afin qu'elles puissent également examiner votre demande.</p>

    <p>Merci de vérifier les données ci-dessous avant de valider votre demande d’aide.</p>
    <br/>

    <form class="form-horizontal" role="form" action="<?php echo url_for("subvention_validation", $subvention) ?>" method="post">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <?php include_partial('subvention/recap', array('subvention' => $subvention)); ?>
        <br />
        <br />
        <div class="row row-margin row-button">
            <div class="col-xs-6">
            	<a href="<?php echo url_for('subvention_engagements', $subvention) ?>" tabindex="-1" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Etape précédente</a>
            </div>
            <div class="col-xs-6 text-right">
                <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span>&nbsp;Soumettre le dossier</button>
            </div>
        </div>
    </form>
</section>

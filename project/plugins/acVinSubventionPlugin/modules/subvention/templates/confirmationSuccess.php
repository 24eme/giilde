<?php include_partial('subvention/breadcrumb', array('subvention' => $subvention, 'isTeledeclarationMode' => $isTeledeclarationMode)); ?>

<section id="principal">

    <h2>Saisie terminée</h2>


    <p>La saisie de votre dossier de préqualification à destination de votre interprofession est terminée.</p>

    <p>Vous devez à présent poursuivre votre demande directement auprès de la Région Occitanie sur le site de la Région</p>

    <h2>Terminer votre demande de subvention</h2>

    <p>Afin de terminer votre demande de subvention, nous vous invitons à vous rendre sur le site de la région Occitanie en suivant le lien ci-dessous :</p>
	<p class="text-center"><br /><a href="https://mesaidesenligne.laregion.fr/" target="_blank"><img src="/images/screen_region_occitanie.jpg" width="250px" alt="ScreenShot Région Occitanier" /><br />Cliquez pour vous rendre sur le site de la région Occitanie</a></p>

    <div class="row">
        <div class="col-xs-6">
            <a class="btn btn-default" tabindex="-1" href="<?php echo url_for('subvention_etablissement', $subvention->getEtablissement()) ?>"><span class="glyphicon glyphicon-chevron-left"></span>&nbsp;Retour à mon espace</a>
        </div>
        <div class="col-xs-6 text-right">
            <a href="https://mesaidesenligne.laregion.fr/" target="_blank" class="btn btn-success">Vers le site de la région Occitanie&nbsp;<span class="glyphicon glyphicon-log-out"></span></a>
        </div>
    </div>
</section>

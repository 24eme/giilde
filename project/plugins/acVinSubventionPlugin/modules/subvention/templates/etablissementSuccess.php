<ol class="breadcrumb">
  <?php if(!$isTeledeclarationMode): ?><li><a href="<?php echo url_for('subvention') ?>">Subvention</a></li><?php else: ?><li>Subvention</li><?php endif; ?>
  <li><a href="<?php echo url_for('subvention_etablissement', array('identifiant' => $etablissement->identifiant)) ?>"><?php echo $etablissement->nom ?> (<?php echo $etablissement->siret ?>)</a></li>

</ol>

<section id="principal">
<?php if(!$isTeledeclarationMode): ?>
  <div class="row" id="formEtablissementChoice">
    <div class="col-xs-12">
      <?php include_component('subvention', 'formEtablissementChoice') ?>
    </div>
  </div>
<?php endif; ?>

<div class="row">
    <div class="col-xs-9">
      <div class="panel panel-default">
          <div class="panel-heading">
            <h2 class="panel-title">Contrat Relance Viti Occitanie</h2>
          </div>
          <div class="panel-body">
            <p>Bienvenue sur la plateforme de dépôt des demandes de subventions auprès de votre interprofession dans le cadre du Contrat Relance Viti de la Région Occitanie.</p>

            <p>Dans le contexte de la crise sanitaire et économique liée au Covid-19 et afin de protéger les entreprises des effets de la crise et accompagner la reprise commerciale de ce secteur essentiel à l’économie régionale, la Région Occitanie et les acteurs régionaux de la filière viti-vinicole ont engagé collectivement l’élaboration partenariale d’un plan de relance de la filière viti-vinicole régionale.</p>

            <p>Il s’agit d’un dispositif d’aides financées par la Région Occitanie en faveur des entreprises de la filière viti-vinicole régionale Occitanie pour les aider à réaliser des actions de promotion et communication, afin de dynamiser les ventes des vins régionaux AOP et IGP.</p>

            <p>Le dépôt d’un dossier de demande d’aide auprès de la Région Occitanie s’effectue de manière dématérialisée, via les plateformes interprofessionnelles et le portail des aides Région.</p>

            <p>La saisie du dossier successivement sur les deux plateformes (plateforme de l’interprofession puis portail des aides de la Région) est nécessaire pour que qu’il soit recevable.</p>

            <p>Pour plus de précisions, vous pouvez consulter la Notice d'Informations dans l'encart Documentations ci-contre.</p>
          </div>
          <div class="panel-footer text-right">
            <a href="<?php echo url_for('subvention_creation',array('identifiant' => $etablissement->identifiant, 'operation' => SubventionConfiguration::getInstance()->getOperationEnCours())); ?>" class="btn btn-primary">Démarrer la demande</a>
          </div>
      </div>
    </div>
    <div class="col-xs-3">
        <?php include_partial('subvention/aide'); ?>
    </div>
  </div>
</section>

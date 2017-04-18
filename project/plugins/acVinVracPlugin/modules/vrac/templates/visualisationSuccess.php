<?php
/* Fichier : recapitulatifSuccess.php
 * Description : Fichier php correspondant à la vue de vrac/XXXXXXXXXXX/recapitulatif
 * Affichage des dernières information de la saisie : numero de contrat
 * Auteur : Petit Mathurin - mpetit[at]actualys.com
 * Version : 1.0.0
 * Derniere date de modification : 29-05-12
 */
use_helper('Vrac');
use_helper('Date');
?>

<ol class="breadcrumb">
  <?php if ($isTeledeclarationMode): ?>
    <li><a href="<?php echo url_for('vrac_societe', array('identifiant' => $etablissementPrincipal->identifiant)); ?>" class="active">Contrats</a></li>
  <?php else: ?>
    <li><a href="<?php echo url_for('vrac') ?>">Contrats</a></li>
  <?php endif; ?>
    <li><a href="" class="active">Visualisation du contrat n° <?php echo $vrac->numero_archive; ?> <?php if ($vrac->numero_archive == $vrac->numero_contrat) echo '('.formatNumeroBordereau($vrac->numero_contrat).')'; ?></a></li>
</ol>
<section id="principal" class="vrac">
<div class="row">
    <div class="col-xs-12">
        <h2 class="titre_page">
            <?php if ($isTeledeclarationMode): ?>
                <span>Visualisation du contrat</span>
            <?php endif; ?>
            <div class="statut_contrat">

                <?php
                if ($isTeledeclarationMode):
                    $classStatut = strtolower($vrac->valide->statut);
                    if (($vrac->valide->statut == VracClient::STATUS_CONTRAT_ATTENTE_SIGNATURE) && $vrac->isSocieteHasSigned($societe)) {
                        $classStatut = 'attente_signature_autres';
                    }
                    ?>
                    <span class="<?php echo $classStatut; ?>"><?php echo "Contrat de " . showType($vrac) . '&nbsp;-&nbsp;' . $vrac->getTeledeclarationStatutLabel(); ?></span>
                <?php endif; ?>
            </div>
        </h2>
    </div>

    <div class="col-xs-4 text-left">
        <p>
            <span class="<?php echo typeToPictoCssClass($vrac->type_transaction) ?>" style="font-size: 24px;"><?php echo "&nbsp;Contrat de " . showType($vrac); ?></span>
        </p>
    </div>

    <?php if ($vrac->isVise()) : ?>
      <div class="col-xs-4 text-center">
      <?php if (!preg_match('/^DRM/', $vrac->numero_archive)): ?>
            <h3>N° <?php echo $vrac->numero_archive; ?> (<?php echo format_date($vrac->date_visa, "dd/MM/yyyy", "fr_FR"); ?>)</h3>
                <small class="text-muted"><?php echo formatNumeroBordereau($vrac->numero_contrat); ?></small>
            </p>
      <?php endif; ?>
      </div>
        <div class="col-xs-4 text-right">
            <form id="vrac_condition" method="post" action="<?php echo url_for('vrac_visualisation', $vrac) ?>">
                <div class="btn-group">
                    <span style="background-color: #e6e6e6; border-color: #adadad; color: #333; cursor: default; font-weight: bold;" class="btn btn-default btn-disabled  statut  <?php echo getClassStatutPicto($vrac, $isTeledeclarationMode); ?>"><?php echo $vrac->getStatutLabel(); ?></span>
                    <?php if (!$isTeledeclarationMode): ?>

                        <?php if ($vrac->valide->statut != VracClient::STATUS_CONTRAT_ANNULE) : ?>
                            <?php if ($vrac->valide->statut == VracClient::STATUS_CONTRAT_SOLDE) : ?>
                                <a href="<?php echo url_for('vrac_nonsolder', $vrac) ?>" class="btn btn-default">Désolder</a>
                            <?php endif; ?>
                            <?php if ($vrac->valide->statut == VracClient::STATUS_CONTRAT_NONSOLDE) : ?>
                                <a href="<?php echo url_for('vrac_solder', $vrac) ?>" class="btn btn-default">Solder</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php
                    if (!is_null($vrac->valide->statut) && $vrac->valide->statut != VracClient::STATUS_CONTRAT_ANNULE && (is_null($vrac->volume_enleve) || ($vrac->volume_enleve == 0))):
                        if (!$isTeledeclarationMode):
                            ?>
                            <a id="btn_editer_contrat" href="<?php echo url_for('vrac_soussigne', $vrac); ?>" class="btn btn-warning">Modifier</a>
                        <?php endif; ?>
                        <?php if ($isTeledeclarationMode && $isTeledeclare && $isProprietaire && !$vrac->isVise() && $vrac->valide->statut != VracClient::STATUS_CONTRAT_VALIDE): ?>
                            <button onclick='return confirm("Étes-vous sur de vouloir supprimer ce contrat ?")' id="btn_annuler_contrat" type="submit" class="btn btn-danger">Annuler</button>
                        <?php endif; ?>
                        <?php if (!$isTeledeclarationMode): ?>
                            <button onclick='return confirm("Étes-vous sur de vouloir supprimer ce contrat ?")' id="btn_annuler_contrat" type="submit" class="btn btn-danger">Annuler</button>
                        <?php endif; ?>
                    <?php elseif($vrac->isVracCreation() && !$isTeledeclarationMode): ?>
                      <a id="btn_editer_contrat" href="<?php echo url_for('vrac_soussigne', $vrac); ?>" class="btn btn-warning">Modifier</a>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    <?php endif; ?>
    <?php include_partial("vrac/recap", array('vrac' => $vrac, 'isTeledeclarationMode' => $isTeledeclarationMode, 'enlevements' => $enlevements)); ?>


    <div class="col-xs-12" style="margin-bottom: 20px;">
        <?php if ($vrac->isVise()): ?>
            <div class="txt_centre text-center">
                <a href="<?php echo url_for('vrac_pdf', $vrac) ?>" class="btn btn-success">Télécharger le PDF</a>
            </div>
        <?php endif; ?>
        <?php if ($isTeledeclarationMode && !$vrac->isVise()): ?>
            <a href="<?php echo url_for('vrac_societe', array('identifiant' => str_replace('COMPTE-', '', $societe->compte_societe))); ?>" class="btn btn-default" style="float: left;">Retourner à l'espace contrats</a>
        <?php endif; ?>
        <?php if ($signatureDemande): ?>

            <a data-toggle="modal" data-target="#signature_popup_content" class="signature_popup btn btn-success pull-right">Signer le contrat</a>

        <?php endif; ?>
    </div>
</div>
  <?php if ($signatureDemande): ?>
    <?php include_partial('signature_popup', array('vrac' => $vrac, 'societe' => $societe, 'etablissementPrincipal' => $etablissementPrincipal)); ?>
<?php endif; ?>
</section>

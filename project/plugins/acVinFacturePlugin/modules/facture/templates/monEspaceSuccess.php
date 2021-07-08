<?php
use_helper('Float');
?>
<?php include_partial('facture/preTemplate'); ?>

<ol class="breadcrumb">
    <li class="visited"><a href="<?php if($sf_user->hasCredential(AppUser::CREDENTIAL_ADMIN)): ?><?php echo url_for('facture') ?><?php endif; ?>">Factures</a></li>
    <li class="active"><a href="<?php echo url_for('facture_societe', $societe) ?>" class="active"><?php echo $societe->raison_sociale ?> (<?php echo $societe->identifiant ?>)</a></li>
</ol>

<div class="row">
    <?php if($sf_user->hasCredential(AppUser::CREDENTIAL_ADMIN)): ?>
    <div class="col-xs-12" id="formEtablissementChoice">
        <?php include_component('facture', 'chooseSociete', array('identifiant' => $societe->identifiant)); ?>
    </div>
    <?php endif; ?>
    <div class="col-xs-12">
        <?php include_partial('historiqueFactures', array('societe' => $societe, 'factures' => $factures)); ?>
        <?php if($sf_user->hasCredential(AppUser::CREDENTIAL_ADMIN)): ?>
         <hr />
        <?php
        try {
            $no_region = ! count($societe->getRegionsViticoles());
            include_partial('facture/mouvements', array('mouvements' => $mouvements, 'societe' => $societe, 'form' => $form));
        }catch(Exception $e) {
            echo "<p><i>Societé n'ayant pas de région (ou hors région), impossible d'afficher ses éventuels mouvements passés.</i></p>";
        }
        ?>
        <?php endif; ?>
    </div>
</div>
<?php include_partial('facture/postTemplate'); ?>

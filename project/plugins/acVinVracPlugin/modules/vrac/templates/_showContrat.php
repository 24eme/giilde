<?php
$isValidation = (is_null($vrac->valide->statut));
$isPrixVariable = (!$vrac->prix_total);
$liClass = ($isValidation) ? '' : 'class="lightpadding"';
$template_validation = (isset($template_validation))? $template_validation : false;
?>
<ul>
    <li <?php echo $liClass ?> >
        <div class="style_label">1. Les soussignés</div>
        <div id="soussigne_recapitulatif">
            <?php
            include_partial('soussigneRecapitulatif', array('vrac' => $vrac, 'societe' => $societe, 'template_validation' => $template_validation,  'isTeledeclarationMode' => $isTeledeclarationMode));
            ?>
        </div>
        <?php if($isValidation && !$isTeledeclarationMode): ?>
            <div class="btnModification f_right">
                <a href="<?php echo url_for('vrac_soussigne', $vrac); ?>" class="btn_majeur btn_modifier">Modifier</a>
            </div>
            <?php
            endif;
        ?>
    </li>
    <li <?php echo $liClass ?> >
        <div class="style_label">2. Le marché</div>
        <section id="marche_recapitulatif">
            <?php
            include_partial('marcheRecapitulatif', array('vrac' => $vrac,'isTeledeclarationMode' => $isTeledeclarationMode ));
            ?>
        </section>
        <?php
        if (!$isTeledeclarationMode && !$vrac->isTeledeclare() && ($isValidation || $isPrixVariable)) :
            ?>
            <div class="btnModification f_right">
                <a href="<?php echo url_for('vrac_marche', $vrac); ?>" class="btn_majeur btn_modifier">Modifier</a>
            </div>
            <?php
        endif;
        ?>
    </li>
    <li <?php echo $liClass ?> >
        <div class="style_label">3. Les conditions</div>
        <section id="conditions_recapitulatif">
            <?php
            include_partial('conditionsRecapitulatif', array('vrac' => $vrac,'isTeledeclarationMode' => $isTeledeclarationMode ));
            ?>
        </section>
        <?php
        if (!$vrac->isTeledeclare() && ($isValidation)):
            ?>
            <div class="btnModification f_right">
                <a href="<?php echo url_for('vrac_condition', $vrac); ?>" class="btn_majeur btn_modifier">Modifier</a>
            </div>
            <?php
        endif;
        ?>
    </li>

</ul>

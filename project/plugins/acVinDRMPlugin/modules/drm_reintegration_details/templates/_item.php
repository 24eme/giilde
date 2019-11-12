<?php use_helper('PointsAides'); ?>
<?php $ligneId = "ligne_".str_replace(array("[", "]"), array("-", ""), $form->renderName()) ?>
<tr id="<?php echo $ligneId ?>">
    <td class="form-group <?php if($form['identifiant']->hasError()): ?>has-error<?php endif; ?>  col-xs-5">
        <?php echo $form['identifiant']->renderError(); ?>
        <?php echo $form['identifiant']->render(array("class" => "form-control select2", "autofocus" => "autofocus")); ?>
    </td>
    <td class="form-group <?php if($form['volume']->hasError()): ?>has-error<?php endif; ?> col-xs-5 volume" >
        <?php echo $form['volume']->renderError(); ?>
        <div class="input-group" class="">
            <?php echo $form['volume']->render(array("class" => "input-float form-control text-right")); ?>
            <div class="input-group-addon">hl</div>
        </div>
    </td>
    <td class="text-right col-xs-2">
        <a type="button" data-line="#<?php echo $ligneId ?>" data-add="#drm_reintegration_details_table .dynamic-element-add" data-add="#drm_reintegration_details_table" data-lines="#drm_reintegration_details_table tbody tr" tabindex="-1" class="btn btn-xs btn-danger dynamic-element-delete"><span class="glyphicon glyphicon-remove"></span></a><?php echo getPointAideHtml('drm','mouvements_contrats_supprimer') ?>
    </td>
</tr>

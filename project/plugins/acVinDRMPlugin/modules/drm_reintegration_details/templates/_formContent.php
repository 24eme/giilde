<?php use_helper('Float'); ?>
<?php use_helper('PointsAides'); ?>
<?php
    echo $form->renderHiddenFields();
    echo $form->renderGlobalErrors();
?>
<table id="drm_reintegration_details_table" class="table table-striped">
    <thead>
        <tr>
            <th class="col-xs-5">Date de mise à la consommation</th>
            <th class="col-xs-5">Volumes</th>
            <th class="col-xs-2"></th>
        </tr>
    </thead>
    <tbody class="drm_details_tableBody">
    <?php
    foreach ($form as $itemForm){
        if($itemForm instanceof sfFormFieldSchema) {
                include_partial('item', array('form' => $itemForm, 'detail' => $detail, 'isTeledeclarationMode' => $isTeledeclarationMode));
         } else {
            $itemForm->renderRow();
        }
    }
    ?>
    </tbody>
    <tfoot>
        <tr>
            <td class="col-xs-5"></td>
            <td class="text-right col-xs-5">
                <div class="input-group">
                    <div class="input-group-addon">&Sigma;</div>
                    <input type="text" class="form-control input-float text-right drm_details_volume_total" data-decimal="4" readonly="readonly"  tabindex="-1"  value="<?php echo sprintFloat($detail->get($catKey)->get($key) > 0 ? $detail->get($catKey)->get($key) : "0.00") ?>" />
                    <div class="input-group-addon">hl</div>
                </div>
            </td>
            <td class="text-right col-xs-2"><button type="button" data-container="#drm_reintegration_details_table tbody" data-template="#template_reintegration" class="btn btn-xs btn-default dynamic-element-add"><span class="glyphicon glyphicon-plus"></span></button><?php echo getPointAideHtml('drm','mouvements_contrats_ajouter') ?></td>
        </tr>
    </tfoot>
</table>

<script>
     $('.drm_details_tableBody').on('keyup','td.volume', $.majSommeLabelBind);
</script>

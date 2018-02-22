<?php use_helper('Float'); ?>
<?php use_helper('PointsAides'); ?>
<?php
echo $form->renderHiddenFields();
echo $form->renderGlobalErrors();
?>

<table id="drm_creationvrac_details_table" class="table table-striped">
    <thead>
        <tr class="row">
            <th>Acheteur<?php echo getPointAideHtml('drm','mouvements_contrats_acheteur') ?></th>
            <th>Volume<?php echo getPointAideHtml('drm','mouvements_contrats_volume') ?></th>
            <th>Prix en €/hl<?php echo getPointAideHtml('drm','mouvements_contrats_prix') ?></th>
            <th>N° contrat Interloire<?php echo getPointAideHtml('drm','mouvements_contrats_numero_interloire') ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody class="drm_details_tableBody">
        <?php
        foreach ($form as $itemForm) {
            if ($itemForm instanceof sfFormFieldSchema) {
                include_partial('item', array('form' => $itemForm, 'detail' => $detail, 'isTeledeclarationMode' => $isTeledeclarationMode));
            } else {
                $itemForm->renderRow();
            }
        }
        ?>
    </tbody>
    <tfoot>
        <tr class="row">
            <td colspan="2" class="text-right">
                <div class="row">
                    <div class="col-xs-5 col-xs-offset-7">
                        <div class="input-group">
                            <div class="input-group-addon">&Sigma;</div>
                            <input type="text" class="form-control input-float text-right drm_details_volume_total" data-decimal="4" readonly="readonly"  tabindex="-1"  value="<?php echo sprintFloat($detail->get($catKey)->get($key) > 0 ? $detail->get($catKey)->get($key) : "0.00") ?>" />
                            <div class="input-group-addon">hl</div>
                        </div>
                    </div>
                </div>
            </td>
            <td></td>
            <td></td>
            <td class="text-right"><button type="button" data-container="#drm_creationvrac_details_table tbody" data-template="#template_creationvrac" class="btn btn-xs btn-default dynamic-element-add"><span class="glyphicon glyphicon-plus"></span></button><?php echo getPointAideHtml('drm','mouvements_contrats_ajouter') ?></td>
        </tr>
    </tfoot>
</table>

<script>
$('.drm_details_tableBody').on('keyup','td.volume', $.majSommeLabelBind);
</script>

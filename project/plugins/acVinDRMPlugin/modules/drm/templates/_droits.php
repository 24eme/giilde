<?php foreach ($drm->getDroits() as $typedroit => $droits) if (count($drm->droits->{$typedroit})): ?>
    <div class="tableau_ajouts_liquidations">
    <h2>Droits <?php echo $typedroit; ?> <a href="" class="msg_aide" data-msg="help_popup_validation_droit_<?php echo $typedroit; ?>" title="Message aide"></a></h2>
    	<table class="tableau_recap">
            <thead>
    		<tr>
    			<th><strong>Code</strong></th>
    			<th><strong>Volume taxe</strong></th>
    			<th><strong>Volume réintégré</strong></th>
    			<th><strong>Taux</strong></th>
    			<th><strong>Droits à payer</strong></th>
    		</tr>
             </thead>
             <tbody>
             <?php $i=1; foreach ($droits->getDroitsWithVirtual() as $code => $droit) :  $i++;
    $strong = ($droit->isTotal()) ? '<strong>' : '';
    $estrong = ($droit->isTotal()) ? '</strong>' : '';
?>
            <tr <?php if($i%2!=0) echo ' class="alt"'; ?>>
                    <td><?php echo $strong.$droit->getLibelle().$estrong; ?></td>
        	<td class="<?php echo isRectifierCssClass($droit, 'volume_taxe') ?>"><?php echo $strong ; echoFloat($droit->volume_taxe); echo $estrong;?>&nbsp;<span class="unite">hl</span></td>
        	<td class="<?php echo isRectifierCssClass($droit, 'volume_reintegre') ?>"><?php echo $strong; echoFloat($droit->volume_reintegre); echo $estrong; ?>&nbsp;<span class="unite">hl</span></td>
        	<td class=""><?php          if (!$droit->isVirtual()){
                                            echo $strong;
                                            echoFloat($droit->taux);
                                            echo $estrong; 
                             ?>
                            &nbsp;<span class="unite">€/hl</span>
                            <?php 
                            } ?>
                                            
                </td>
        	<td class="<?php echo (isRectifier($droit, 'volume_taxe') || isRectifier($droit, 'volume_reintegre')) ? rectifierCssClass() : null ?>"><strong><?php echoFloat($droit->payable); ?>&nbsp;</strong>&nbsp;<span class="unite">€</span></td>
        		</tr>
        		<?php endforeach; ?>
            </tbody>
    	</table>
    </div>
<?php endif; ?>
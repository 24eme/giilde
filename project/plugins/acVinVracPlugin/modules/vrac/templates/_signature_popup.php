<?php
use_helper('PointsAides');
?>
    <div id="signature_popup_content"  class="modal fade" role="dialog">
       <div class="modal-dialog">
         <div class="modal-content">
     <div class="modal-header">
        <h2>Veuillez confirmer la signature du contrat </h2>
      </div>
   <div class="modal-body">

     <div class="text-center">

       <p>
           <span class="<?php echo typeToPictoCssClass($vrac->type_transaction) ?>" style="font-size: 24px;"><?php echo "&nbsp;Contrat de " . showType($vrac); ?></span>
         </p>
     <?php if (in_array($vrac->type_transaction, array(VracClient::TYPE_TRANSACTION_VIN_VRAC, VracClient::TYPE_TRANSACTION_VIN_BOUTEILLE))): ?>
     <h3><?php echo $vrac->produit_libelle ?> <small><?php echo ($vrac->millesime)? $vrac->millesime : 'Non millésimé'; ?><?php if ($vrac->get('millesime_85_15')): ?> (85/15)<?php endif;?></small></h3>
     <?php if ($vrac->cepage): ?>
         Cépage : <strong><?php echo $vrac->cepage_libelle ?><?php if ($vrac->get('cepage_85_15')): ?> (85/15)<?php endif;?></strong><br />
         <?php endif; ?>
   <?php else: ?>
     <h3><?php echo $vrac->cepage_libelle ?> <small><?php if ($vrac->get('cepage_85_15')): ?> (85/15)<?php endif;?></small></h3>
     <?php if ($vrac->produit_libelle): ?>
         Revendiquable en <strong><?php echo $vrac->produit_libelle ?></strong><br />
         <?php endif; ?>
   <?php endif; ?>
   </div>
   </div>
   <div class="modal-footer">
        <div class="row">
          <div class="col-xs-6 text-left">
            <a data-dismiss="modal" id="signature_popup_close" class="btn btn-danger" style="float: left;" href="#" >Annuler</a><div style="padding-top:6px;"><?php echo getPointAideHtml('vrac','validation_popup_annuler'); ?></div>
          </div>
          <div class="col-xs-6 text-right">

            <?php if (isset($validation) && $validation): ?>
              <button id="signature_popup_confirm" type="submit" class="btn btn-success pull-right" ><span>Signer le contrat</span></button>
            <?php else : ?>
              <a id="signature_popup_confirm" href="<?php echo url_for('vrac_signature', $vrac) ?>" class="btn btn-success pull-right"><span>Signer le contrat</span></a>
            <?php endif; ?>
          </div>
        </div>
    </div>
  </div>
</div>

</div>

<?php $warningsMessages = DRMConfiguration::getInstance()->getWarningsMessagesForProduits($produits);
if (count($warningsMessages)): ?>
<div class="modal fade" id="warningsProduitsModal" role="dialog" tabindex="-1">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Certains produits ajoutés requièrent des attentions particulières&nbsp;:</h4>
      </div>
      <div class="modal-body">
        <ul class="text-danger list-unstyled">
          <?php foreach ($warningsMessages as $msg): ?>
            <?php echo $msg; ?>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
  </div>
    </div>
  </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
      $('#warningsProduitsModal').modal('show');
    });
</script>
<?php endif; ?>

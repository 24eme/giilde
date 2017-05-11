<div class="row">
    <div class="col-xs-12">
        <h3>10 dernières générations <small>(<a href="<?php echo url_for('generation_list', array('type_document' => array(GenerationClient::TYPE_DOCUMENT_FACTURES, GenerationClient::TYPE_DOCUMENT_EXPORT_SHELL, GenerationClient::TYPE_DOCUMENT_VRACSSANSPRIX), 'limite' => 200)); ?>">voir tout</a>)</small></h2>

        <?php if (count($generations) > 0): ?>
            <?php include_partial('generation/list', array('generations' => $generations)) ?>
        <?php else : ?>
            <p>Aucune génération de facture</p>
        <?php endif; ?>
    </div>
</div>

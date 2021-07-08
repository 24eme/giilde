<?php include_partial('facture/preTemplate'); ?>

<h3>Liste des générations</h3>

<?php include_partial('generation/list', array('generations' => $historyGeneration)); ?>

<?php include_partial('facture/postTemplate'); ?>
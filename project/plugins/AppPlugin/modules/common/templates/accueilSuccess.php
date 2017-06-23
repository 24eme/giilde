<div class="row">
    <?php $etablissement = (isset($etablissement)) ? $etablissement : null ?>
    <?php if ($etablissement): ?>
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">
                            <strong><?php echo $etablissement->nom; ?></strong>
                        </div>
                        <div class="text-center panel-body">
                            <?php echo $etablissement->famille; ?>&nbsp;
                            <br/>
                            <small class="text-muted"><?php echo $etablissement->identifiant; ?></small>
                            <br>
                            <small><?php echo $etablissement->siege->adresse . ' - ' . $etablissement->siege->commune . ' ' . $etablissement->siege->code_postal; ?></small>
                            <br>
                            <small class="text-muted">
                                <?php if ($etablissement->isViticulteur()): echo "CVI : " . $etablissement->cvi;
                                endif; ?>
                            </small>
                            <br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php if($teledeclaration): ?>
<?php include_component('common', 'blocksTeledeclaration', array('etablissementPrincipal' => $etablissementPrincipal)); ?>
<?php include_partial('common/blocksTeledeclarationExtra'); ?>
<?php else: ?>
<?php include_component('common', 'blocks', array('etablissement' => $etablissement)); ?>
<?php endif; ?>

</div>

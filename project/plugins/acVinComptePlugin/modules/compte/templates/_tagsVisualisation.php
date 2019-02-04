<?php $btnsize = (!isset($smallBlock)) ? 'btn-sm' : 'btn-xs' ?>
<div class="row">
    <div class="col-xs-12">
        <strong>Tags :</strong>

        <?php foreach ($compte->tags as $type_tag => $selected_tags) : ?>
            <?php foreach ($selected_tags as $t): ?>

                <?php $targs['tags'] = implode(',', array($type_tag . ':' . $t)); ?>
                <?php $targs['tag'] = $t; ?>
                <?php $targs['q'] = $compte->identifiant ?>

                <span class="btn-group">
                    <a href="" title="<?= ucfirst($type_tag) ?>" class="btn <?= $btnsize ?> btn-default active">
                        <?= substr(strtoupper($type_tag), 0, 1); ?>
                    </a>
                    <a class="btn btn-default <?= $btnsize ?>" href="<?= url_for('compte_search', $targs) ?>">
                        <?= str_replace('_', ' ', $t) ?>
                    </a>
                    <?php if ($type_tag == 'manuel'): ?>
                        <a class="btn btn-default <?= $btnsize ?>" href="<?= url_for('compte_removetag', $targs) ?>">
                            <i class="glyphicon glyphicon-trash"></i>
                        </a>
                    <?php endif; ?>
                </span>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <a class="<?= $btnsize ?> btn-default" href="<?= url_for('compte_search', array('q' => $compte->identifiant)) ?>">
            <i class="glyphicon glyphicon-plus"></i>
        </a>
    </div>
</div>

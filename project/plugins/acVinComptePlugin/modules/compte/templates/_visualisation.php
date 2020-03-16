<div class="list-group" id="<?php echo $compte->_id; ?>">
    <div class="list-group-item<?php echo ($compte->isSuspendu()) ? ' disabled': '' ?>">
        <div class="row">
            <div class="col-xs-10">
                <h4 style="margin-top: 5px;"><span class="<?php echo comptePictoCssClass($compte->getRawValue()) ?>"></span> <?php echo ($compte->nom_a_afficher) ? $compte->nom_a_afficher : $compte->nom; ?></h4>
            </div>
            <div class="col-xs-2 text-right">
	            <div class="btn-group">
                    <a class="btn btn-sm dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-pencil"></span> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li<?php echo ($compte->getSociete()->isSuspendu() || $compte->isSuspendu()) ? ' class="disabled"' : '' ; ?>><a href="<?php echo ($compte->getSociete()->isSuspendu() || $compte->isSuspendu()) ? 'javascript:void(0)' : url_for('compte_modification', $compte); ?>">Editer</a></li>
                        <li<?php echo ($compte->getSociete()->isSuspendu() || $compte->isSuspendu()) ? ' class="disabled"' : '' ; ?>><a href="<?php echo ($compte->getSociete()->isSuspendu() || $compte->isSuspendu()) ? 'javascript:void(0)' : url_for('compte_switch_statut', array('identifiant' => $compte->identifiant)); ?>">Suspendre</a></li>
                        <li<?php echo ($compte->getSociete()->isSuspendu() || $compte->isActif()) ? ' class="disabled"' : '' ; ?>><a href="<?php echo ($compte->getSociete()->isSuspendu() || $compte->isActif()) ? 'javascript:void(0)' : url_for('compte_switch_statut', array('identifiant' => $compte->identifiant)); ?>">Activer</a></li>
                    </ul>
	               </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-8">
                <?php if($compte->fonction): ?>
                <span class="label label-primary"><?php echo $compte->fonction; ?></span>&nbsp;
                <?php endif; ?>
                <?php if ($compte->isSuspendu()): ?>
                    <span class="label label-default"><?php echo $compte->statut; ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if ($compte->isSameAdresseThanSociete()): ?>
        <div class="list-group-item list-group-item-xs text-center text-muted<?php echo ($compte->isSuspendu()) ? ' disabled': '' ?>">
            <em>Même adresse que la société</em>
        </div>
    <?php else : ?>
        <div class="list-group-item list-group-item-xs text-center<?php echo ($compte->isSuspendu()) ? ' disabled': '' ?>">
            <div class="row">
                <?php include_partial('compte/adresseVisualisation', array('compte' => $compte, 'modification' => $modification, 'reduct_rights' => $reduct_rights, 'smallBlock' => true)); ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($compte->isSameContactThanSociete()): ?>
        <div class="list-group-item list-group-item-xs text-center text-muted<?php echo ($compte->isSuspendu()) ? ' disabled': '' ?>">
            <em>Même contact que la société</em>
        </div>
    <?php else : ?>
        <div class="list-group-item list-group-item-xs text-center<?php echo ($compte->isSuspendu()) ? ' disabled': '' ?>">
            <div class="row">
                <?php include_partial('compte/contactVisualisation', array('compte' => $compte, 'modification' => $modification, 'reduct_rights' => $reduct_rights, 'smallBlock' => true)); ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($compte->mot_de_passe === null) : ?>
        <div class="list-group-item list-group-item-xs">
            <div class="row">
                <div class="col-xs-12">
                    <i class="glyphicon glyphicon-asterisk"></i> <a href="<?= url_for('compte_generate_codecreation', ['identifiant' => $compte->identifiant]) ?>">Générer un code de création</a>
                </div>
            </div>
        </div>
    <?php elseif (strpos($compte->mot_de_passe, '{TEXT}') === 0): ?>
        <div class="list-group-item list-group-item-xs">
            <div class="row">
                <div class="col-xs-12">
                    <span class=text-muted">Login de télédeclaration : <?php echo $compte->identifiant ?></span><br />
                    <span class=text-muted">Code de création : <?= str_replace('{TEXT}', '', $compte->mot_de_passe) ?></span>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="list-group-item list-group-item-xs">
            <div class="row">
                <div class="col-xs-12">
                    <span class=text-muted">Login de télédeclaration : <?php echo $compte->identifiant ?></span><br />
                    <span class=text-muted">Code de création : Compte déjà créé</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="list-group-item list-group-item-xs<?php echo ($compte->isSuspendu()) ? ' disabled': '' ?>">
        <?php include_partial('compte/tagsVisualisation', array('compte' => $compte, 'modification' => $modification, 'reduct_rights' => $reduct_rights, 'smallBlock' => true)); ?>
    </div>

</div>

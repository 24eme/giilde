<form id="form_ajout" class="form-horizontal" action="<?php echo url_for('produit_modification', array('noeud' => $form->getObject()->getTypeNoeud(), 'hash' => $produit->getHashForKey())) ?>" method="post">
    <?php echo $form->renderGlobalErrors() ?>
    <?php echo $form->renderHiddenFields() ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><label>Code / Libellé</label></h3>
                </div>
                <div class="panel-body">
                                <div class="form-group <?php if ($form['libelle']->hasError()): ?>has-error<?php endif; ?>" >
                                    <?php echo $form['libelle']->renderError() ?>
                                    <?php echo $form['libelle']->renderLabel(null, array('class' => 'col-xs-4')); ?>
                                    <div class="col-xs-8"><?php echo $form['libelle']->render(); ?></div>
                                </div>
                                <div class="form-group <?php if ($form['format_libelle']->hasError()): ?>has-error<?php endif; ?>" >
                                    <?php echo $form['format_libelle']->renderError() ?>
                                    <?php echo $form['format_libelle']->renderLabel(null, array('class' => 'col-xs-4')); ?>
                                    <div class="col-xs-8"><?php echo $form['format_libelle']->render(); ?></div>
                                </div>
                                <div class="form-group">
                                <div class="col-xs-4"><strong>Clé :</strong></div>
                                <div class="col-xs-8">
                                <span><?php echo $form->getObject()->getKey(); ?></span><br/>
                                <span class="text-muted">Cette clé est utilisée pour construire l'arbre, elle est constituante du hash produit</span>
                                </div>
                                </div>
                                <div class="form-group <?php if ($form['code']->hasError()): ?>has-error<?php endif; ?>" >
                                    <?php echo $form['code']->renderError(); ?>
                                    <?php echo $form['code']->renderLabel(null, array('class' => 'col-xs-4')); ?>
                                    <div class="col-xs-8"><?php echo $form['code']->render(); ?></div>
                                </div>
                    <?php if ($form->getObject()->exist('densite')): ?>
                                    <div class="form-group <?php if ($form['densite']->hasError()): ?>has-error<?php endif; ?>" >
                                    <?php echo $form['densite']->renderError(); ?>
                                        <?php echo $form['densite']->renderLabel(null, array('class' => 'col-xs-4')); ?>
                                        <div class="col-xs-8"><?php echo $form['densite']->render(); ?>
                                        <span class="text-muted"><?php echo $form['densite']->renderHelp() ?></span></div>
                                    </div>
                    <?php endif; ?>
                    <?php if ($form->getObject()->hasCodes()): ?>
                                    <div class="form-group <?php if ($form['code_produit']->hasError()): ?>has-error<?php endif; ?>" >
                                        <?php echo $form['code_produit']->renderError() ?>
                                        <?php echo $form['code_produit']->renderLabel(null, array('class' => 'col-xs-4')); ?>
                                        <div class="col-xs-8"><?php echo $form['code_produit']->render(); ?></div>
                                    </div>
                                    <div class="form-group <?php if ($form['code_douane']->hasError()): ?>has-error<?php endif; ?>" >
                                        <?php echo $form['code_douane']->renderError() ?>
                                        <?php echo $form['code_douane']->renderLabel(null, array('class' => 'col-xs-4')); ?>
                                        <div class="col-xs-8"><?php echo $form['code_douane']->render(); ?></div>
                                    </div>
                                    <div class="form-group <?php if ($form['code_comptable']->hasError()): ?>has-error<?php endif; ?>" >
                                        <?php echo $form['code_comptable']->renderError() ?>
                                        <?php echo $form['code_comptable']->renderLabel(null, array('class' => 'col-xs-4')); ?>
                                        <div class="col-xs-8"><?php echo $form['code_comptable']->render(); ?></div>
                                    </div>
                    <?php endif; ?>
                                <div class="form-group <?php if ($form['produit_non_interpro']->hasError()): ?>has-error<?php endif; ?>" >
                                    <?php echo $form['produit_non_interpro']->renderError() ?>
                                    <?php echo $form['produit_non_interpro']->renderLabel(null, array('class' => 'col-xs-4')); ?>
                                    <div class="col-xs-8"><?php echo $form['produit_non_interpro']->render(); ?></div>
                                </div>
                </div>
            </div>
        </div>
        <?php if ($form->getObject()->hasDepartements()): ?>
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><label>Départements</label></h3>
                    </div>
                    <div class="panel-body">
                                <span>Liste des départements :</span>

                                <div class="subForm contenu_onglet" id="formsDepartement">

                                    <?php foreach ($form['secteurs'] as $subform): ?>
                                        <?php include_partial('produit/subformDepartement', array('form' => $subform)) ?><br />
                                    <?php endforeach; ?>
                                    <a href="javascript:void(0)" class="btn_majeur btn_orange">Ajouter une ligne</a>
                                </div>
                                <input class="counteur" type="hidden" name="nb_departement" value="<?php echo count($form['secteurs']) ?>" />
                    </div>
                </div>

            </div>
        <?php endif; ?>
        <?php if ($form->getObject()->hasDroit(ConfigurationDroits::DROIT_DOUANE) && is_a($form->getObject(), "ConfigurationGenre")): ?>
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><label>Droits circulation</label></h3>
                    </div>
                    <div class="panel-body">
                                <?php foreach ($form['droit_douane'] as $subform): ?>
                                    <?php include_partial('produit/subformDroits', array('form' => $subform)) ?>
                                <?php endforeach; ?>
                                <a href="javascript:void(0)" class="btn_majeur btn_orange">Ajouter une ligne</a>
                            <input class="counteur" type="hidden" name="nb_douane" value="<?php echo count($form['droit_douane']) ?>" />
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if($form->getObject()->exist('cepages_autorises') && count($form->getObject()->cepages_autorises)): ?>
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><label>Cépages Autorisés</label></h3>
                    </div>
                    <div class="panel-body">
                      <div class="form-group <?php if ($form['cepages_autorises']->hasError()): ?>has-error<?php endif; ?>" >
                          <?php echo $form['cepages_autorises']->renderError() ?>
                          <?php echo $form['cepages_autorises']->renderLabel(null, array('class' => 'col-xs-4')); ?>
                          <div class="col-xs-8"><?php echo $form['cepages_autorises']->render(); ?></div>
                      </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($form->getObject()->hasDroit(ConfigurationDroits::DROIT_CVO)): ?>
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><label>Cotisations interprofessionnelles&nbsp;&nbsp;</label></h3>
                    </div>
                    <div class="panel-body">
                                <?php foreach ($form['droit_cvo'] as $subform): ?>
                                    <?php include_partial('produit/subformDroits', array('form' => $subform)) ?>
                                <?php endforeach; ?>
                            <input class="counteur" type="hidden" name="nb_cvo" value="<?php echo count($form['droit_cvo']) ?>" />
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($form->getObject()->hasLabels()): ?>
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><label>Labels&nbsp;&nbsp;</label></h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div id="formsLabel">
                                <?php foreach ($form['labels'] as $subform): ?>
                                    <?php include_partial('produit/subformLabel', array('form' => $subform)) ?>
                                <?php endforeach; ?>
                                <a href="javascript:void(0)" class="btn_majeur btn_orange"></a>
                            </div>
                            <input class="counteur" type="hidden" name="nb_label" value="<?php echo count($form['labels']) ?>" />
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($form->getObject()->hasDetails()): ?>
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><label>Activation des lignes&nbsp;&nbsp;</label></h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div id="formsDetails">
                                <?php foreach ($form['detail'] as $detail): ?>
                                    <?php foreach ($detail as $type): ?>
                                        <div class="ligne_form">
                                            <?php if ($type['readable']->hasError()) { ?><span class="error"><?php echo $type['readable']->renderError() ?></span><?php } ?>				<?php echo $type['readable']->renderLabel() ?>
                                            <?php echo $type['readable']->render() ?>
                                            <?php echo $type['writable']->render() ?>
                                        </div>
                                        <br />
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
         <div class="col-sm-12">
            <a name="annuler" class="btn btn-default" href="<?php echo url_for('produits') ?>">Annuler</a>
            <button  name="valider" class="btn btn-success pull-right" type="submit">Valider</button>
        </div>
    </div>
</form>
<?php
include_partial('templateformsDepartement');
include_partial('templateformsDouane');
include_partial('templateformsCvo');
include_partial('templateformsLabel');
?>

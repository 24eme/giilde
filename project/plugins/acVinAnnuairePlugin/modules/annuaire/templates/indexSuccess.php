<?php
use_helper('PointsAides');
?>
<section id="principal">
  <ol class="breadcrumb">
      <li><a href="<?php echo url_for('vrac_societe', array('identifiant' => $etablissementPrincipal->identifiant)); ?>" class="active">Contrats</a></li>
      <li><a href="<?php echo url_for('annuaire', array('identifiant' => $etablissementPrincipal->identifiant)); ?>" class="active">Annuaire</a></li>
  </ol>

    <h2>Annuaire de vos contacts</h2>

    <div class="fond">
        <div class="annuaire clearfix">

            <div class="row">
              <div class="col-xs-12">
                <div class="text-right" >
                    <a href="<?php echo url_for('annuaire_selectionner', array('type' => 'recoltants', 'identifiant' => $etablissement->identifiant)) ?>" class="btn btn-default">Ajouter un viticulteur</a>
                </div>
              </div>
              <br/><br/><br/>
              <div class="col-xs-12">
              <div class="panel panel-default">
                  <div class="panel-heading">
                    <strong>Viticulteurs</strong><span class="badge pull-right"><?php echo count($annuaire->recoltants) ?></span>
                    </div>
                    <div class="panel-body">
                      <ul class="list-group">
                        <?php if (count($annuaire->recoltants) > 0): ?>
                            <?php foreach ($annuaire->recoltants as $key => $item): ?>
                              <li class="list-group-item <?php if ($item->isActif): ?> actif <?php else: ?> list-group-item-danger <?php endif; ?>">
                                    <div class="row">
                                        <div class="col-xs-10"><?php echo $item->name ?><span>&nbsp;(<?php echo str_replace("ETABLISSEMENT-","",$key); ?>)</span></div>
                                        <div class="col-xs-2 text-right"><a href="<?php echo url_for('annuaire_supprimer', array('type' => 'recoltants', 'id' => $key, 'identifiant' => $etablissement->identifiant)) ?>" onclick="return confirm('Confirmez-vous la suppression du viticulteur ?')" class="btn btn-xs btn-danger">X</a>&nbsp;<?php echo getPointAideHtml('vrac','annuaire_suppression'); ?></div>

                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                          <div class="row">
                              <div class="col-xs-12">Aucun viticulteur</div>
                          </div>
                        <?php endif; ?>
                      </ul>
                </div>
            </div>
          </div>
            </div>
              <div class="row">
                <div class="col-xs-12">
                  <div class="text-right" >
                      <a href="<?php echo url_for('annuaire_selectionner', array('type' => 'negociants', 'identifiant' => $etablissement->identifiant)) ?>" class="btn btn-default">Ajouter un négociant</a>
                  </div>
                </div>
                <br/><br/><br/>
                <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                      <strong>Négociants</strong><span class="badge pull-right"><?php echo count($annuaire->negociants) ?></span>
                    </div>
                    <div class="panel-body">
                      <ul class="list-group">
                      <?php if (count($annuaire->negociants) > 0): ?>
                        <?php foreach ($annuaire->negociants as $key => $item): ?>
                          <li class="list-group-item <?php if ($item->isActif): ?> actif <?php else: ?> list-group-item-danger <?php endif; ?>">
                                <div class="row">
                                    <div class="col-xs-10"><?php echo $item->name ?><span>&nbsp;(<?php echo str_replace("ETABLISSEMENT-","",$key); ?>)</span></div>
                                    <div class="col-xs-2 text-right"><a href="<?php echo url_for('annuaire_supprimer', array('type' => 'negociants', 'id' => $key, 'identifiant' => $etablissement->identifiant)) ?>" onclick="return confirm('Confirmez-vous la suppression du négociant ?')" class="btn btn-xs btn-danger">X</a>&nbsp;<?php echo getPointAideHtml('vrac','annuaire_suppression'); ?></div>

                                </div>
                            </li>
                        <?php endforeach; ?>
                            <?php else: ?>
                              <div class="row">
                                  <div class="col-xs-12">Aucun négociant</div>
                              </div>
                            <?php endif; ?>
                          </ul>
                    </div>
                </div>
                </div>
              </div>

              <div class="row">
                <div class="col-xs-12">
                  <div class="text-right" >
                      <a href="<?php echo url_for('annuaire_commercial_ajouter', array('identifiant' => $etablissement->identifiant)) ?>" class="btn btn-default">Ajouter un commercial</a>
                  </div>
                </div>
                <br/><br/><br/>
                <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                      <strong>Commerciaux</strong><span class="badge pull-right"><?php echo count($annuaire->commerciaux) ?></span>
                    </div>
                    <div class="panel-body">
                      <ul class="list-group">
                      <?php if (count($annuaire->commerciaux) > 0): ?>
                        <?php foreach ($annuaire->commerciaux as $key => $item): ?>
                          <li class="list-group-item <?php if ($item->isActif): ?> actif <?php else: ?> list-group-item-danger <?php endif; ?>">
                                <div class="row">
                                    <div class="col-xs-10"><?php echo $item->name ?><span>&nbsp;(<?php echo str_replace("ETABLISSEMENT-","",$key); ?>)</span></div>
                                    <div class="col-xs-2 text-right"><a href="<?php echo url_for('annuaire_supprimer', array('type' => 'commerciaux', 'id' => $key, 'identifiant' => $etablissement->identifiant)) ?>" onclick="return confirm('Confirmez-vous la suppression du commercial ?')" class="btn btn-xs btn-danger">X</a>&nbsp;<?php echo getPointAideHtml('vrac','annuaire_suppression'); ?></div>

                                </div>
                            </li>
                        <?php endforeach; ?>
                            <?php else: ?>
                              <div class="row">
                                  <div class="col-xs-12">Aucun commercial</div>
                              </div>
                            <?php endif; ?>
                          </ul>
                    </div>
                </div>
                </div>
              </div>
        </div>
    </div>

    <a class="btn btn-default" href="<?php echo url_for('vrac_societe', array('identifiant' => $etablissement->identifiant)) ?>">Retourner à l'espace contrats</a>
    <?php echo getPointAideHtml('vrac','annuaire_fil_saisi_retour_contrat'); ?>
</section>

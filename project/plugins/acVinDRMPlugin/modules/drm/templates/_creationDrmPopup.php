<?php use_helper('Date'); ?>
<?php use_helper('DRM'); ?>
<?php use_helper('Orthographe'); ?>
<?php use_helper('PointsAides'); ?>
<div id="drm_nouvelle_<?php echo $periode . '_' . $identifiant; ?>" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form action="<?php echo url_for('drm_choix_creation', array('identifiant' => $identifiant, 'periode' => $periode)); ?>" method="post" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h2>Création de la DRM <?php echo getFrPeriodeElision($periode); ?></h2>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <?php echo $drmCreationForm->renderHiddenFields(); ?>
                        <?php echo $drmCreationForm->renderGlobalErrors(); ?>

                        <div class="ligne_form type_creation" id="type_creation_div_<?php echo $periode . '_' . $identifiant; ?>" >
                            <?php echo $drmCreationForm['type_creation']->renderError(); ?>
                            <ul class="list-unstyled">
                                <?php if(DRMConfiguration::getInstance()->getRepriseDonneesUrl()): ?>
                                    <li class="col-xs-10 col-xs-offset-1">
                                        <input type="radio" checked="checked" id="drmChoixCreation_type_creation_<?php echo DRMClient::DRM_CREATION_DOCUMENTS.'_'.$periode . '_' . $identifiant; ?>" value="<?php echo DRMClient::DRM_CREATION_DOCUMENTS; ?>" name="drmChoixCreation[type_creation]">&nbsp;<label for="drmChoixCreation_type_creation_<?php echo DRMClient::DRM_CREATION_DOCUMENTS.'_'.$periode . '_' . $identifiant; ?>">Création d'une drm pré-remplie</label>
                                            <div class="pull-right"><?php echo getPointAideHtml('drm','creation_nouvelle') ?></div>
                                    </li>
                                <?php else: ?>
                                    <li class="col-xs-10 col-xs-offset-1">
                                            <input type="radio" checked="checked" id="drmChoixCreation_type_creation_<?php echo DRMClient::DRM_CREATION_VIERGE.'_'.$periode . '_' . $identifiant; ?>" value="<?php echo DRMClient::DRM_CREATION_VIERGE; ?>" name="drmChoixCreation[type_creation]">&nbsp;<label for="drmChoixCreation_type_creation_<?php echo DRMClient::DRM_CREATION_VIERGE.'_'.$periode . '_' . $identifiant; ?>">Création d'une drm vierge</label>
                                                <div class="pull-right"><?php echo getPointAideHtml('drm','creation_nouvelle') ?></div>
                                    </li>
                                <?php endif; ?>
                                    <li class="col-xs-10 col-xs-offset-1" >
                                        <input type="radio" id="drmChoixCreation_type_creation_<?php echo DRMClient::DRM_CREATION_NEANT.'_'.$periode . '_' . $identifiant; ?>" value="<?php echo DRMClient::DRM_CREATION_NEANT; ?>" name="drmChoixCreation[type_creation]">&nbsp;<label for="drmChoixCreation_type_creation_<?php echo DRMClient::DRM_CREATION_NEANT.'_'.$periode . '_' . $identifiant; ?>">Création d'une drm à néant</label>
                                        <div class="pull-right"><?php echo getPointAideHtml('drm','creation_vierge') ?></div>
                                    </li>

                                    <li class="col-xs-10 col-xs-offset-1" >
                                        <input type="radio" id="drmChoixCreation_type_creation_<?php  echo DRMClient::DRM_CREATION_EDI.'_'.$periode . '_' . $identifiant; ?>" value="CREATION_EDI" name="drmChoixCreation[type_creation]">&nbsp;<label for="drmChoixCreation_type_creation_<?php echo DRMClient::DRM_CREATION_EDI.'_'.$periode . '_' . $identifiant; ?>">Création depuis un logiciel tiers</label>
                                        <div class="pull-right"><?php echo getPointAideHtml('drm','creation_edi') ?></div>
                                        <div style="display: none;" class="ligne_form" id="file_edi_div_<?php echo $periode . '_' . $identifiant; ?>">
                                            <span>
                                                <?php echo $drmCreationForm['file']->renderError(); ?>
                                                <?php echo $drmCreationForm['file']->renderLabel() ?>
                                                <?php echo $drmCreationForm['file']->render(array('class' => 'drmChoixCreation_file')); ?>
                                            </span>
                                        </div>
                                    </li>
                                    <?php if(DRMConfiguration::getInstance()->getRepriseDonneesUrl()): ?>
                                        <li class="row col-xs-10 col-xs-offset-1 loading-msg" style="display:none;">
                                            <div class="row">
                                                <div class="col-xs-1">
                                                    <div class="loader"></div>
                                                </div>
                                                <div class="col-xs-11">
                                                    <p>La génération de votre DRM est en cours. Merci de patienter.<br/><small>La procédure peut prendre 30 secondes</small></p>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a id="drm_nouvelle_popup_close" class="btn btn-danger pull-left popup_close" data-dismiss="modal" style="float: left;" href="#" >Annuler</a>
                        <button id="drm_nouvelle_popup_confirm" type="submit" class="btn btn-success pull-right" style="float: right;" ><span>Commencer la DRM</span></button>
                    </div>
            </form>
        </div>
    </div>
</div>

<?php if(DRMConfiguration::getInstance()->getRepriseDonneesUrl()): ?>
    <script type="text/javascript">
    $("button#drm_nouvelle_popup_confirm").click(function(){
        $("a#drm_nouvelle_popup_close").css("opacity","0.7");
        $(this).attr("disabled","disabled");
        $(".loading-msg").show();
        $(this).parents('form').submit();

        return false;
    });
    </script>
<?php endif; ?>

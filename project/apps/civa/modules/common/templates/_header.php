<?php if(sfConfig::get('app_url_header')): ?>
    <?php $isAdmin = ($sf_user->hasCredential(AppUser::CREDENTIAL_ADMIN) || $sf_user->isUsurpationCompte()); ?>
    <?php $compteOrigine = $sf_user->getCompteOrigin()->login; ?>
    <?php $compte = null; ?>
    <?php if($sf_request->getAttribute('sf_route')->getRawValue() instanceof InterfaceEtablissementRoute): $compte = $sf_request->getAttribute('sf_route')->getEtablissement()->getMasterCompte()->identifiant;  endif; ?>
    <?php if(!$compte && $sf_request->getAttribute('sf_route')->getRawValue() instanceof InterfaceSocieteRoute): $compte = $sf_request->getAttribute('sf_route')->getSociete()->getMasterCompte()->identifiant; endif; ?>

    <?php echo file_get_contents(sfConfig::get('app_url_header')."?compte=".$compte."&isAdmin=".$isAdmin."&compteOrigine=".$compteOrigine); ?>

    <div id="main">
        <?php if($compte): ?>
        <div id="nav">
            <?php echo file_get_contents(sfConfig::get('app_url_nav')."?compte=".$compte); ?>
        </div>
        <?php else: ?>
            <div style="height: 20px;"></div>
        <?php endif; ?>
<?php else: ?>
<div id="main">
    <nav class="navbar navbar-default navbar-static-top">
          <div class="container">
            <?php include_component('common', 'nav'); ?>
        </div>
    </nav>
<?php endif; ?>

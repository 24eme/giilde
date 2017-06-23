<?php include_component('common', 'navItem', array(
            'libelle' => 'Accueil',
            'prefix' => 'common_accueil',
            'route' => 'common_accueil',
            'route_etablissement' => 'common_accueil_etablissement',
            'etablissement' => $etablissement,
            'target' => '_self'
     )); ?>
<div id="navbar" class="navbar-collapse collapse">
<ul class="nav navbar-nav">
    <?php if ($sf_user->hasCredential('transactions')) :
     include_component('common', 'navItem', array(
            'libelle' => 'DRM',
            'prefix' => 'drm',
            'route' => 'drm',
            'route_etablissement' => 'drm_etablissement',
            'etablissement' => $etablissement,
            'target' => '_self'
     ));

     include_component('common', 'navItem', array(
            'libelle' => 'Contrats',
            'prefix' => 'vrac',
            'route' => 'vrac',
            'route_etablissement' => 'vrac_recherche',
            'etablissement' => $etablissement,
            'target' => '_self'
     ));

     if(SV12Configuration::getInstance()->isActif()):
     include_component('common', 'navItem', array(
            'libelle' => 'SV12',
            'prefix' => 'sv12',
            'route' => 'sv12',
            'route_etablissement' => 'sv12_etablissement',
            'etablissement' => $etablissement,
            'target' => '_self'
     ));
    endif;

      include_component('common', 'navItem', array(
            'libelle' => 'Factures',
            'prefix' => 'facture',
            'route' => 'facture',
            'route_etablissement' => 'facture_etablissement',
            'etablissement' => $etablissement,
            'target' => '_self'
      ));

       include_component('common', 'navItem', array(
            'libelle' => 'Stocks',
            'prefix' => 'stocks',
            'route' => 'stocks',
            'route_etablissement' => 'stocks_etablissement',
            'etablissement' => $etablissement,
            'target' => '_self'
       ));

       /*include_component('common', 'navItem', array(
            'libelle' => 'Alertes',
            'prefix' => 'alerte',
            'route' => 'alerte',
            'route_etablissement' => 'alerte_etablissement',
            'etablissement' => $etablissement,
            'target' => '_self'
       ));*/
   endif;


   if ($sf_user->hasCredential('contacts')) :
       include_component('common', 'navItem', array(
            'libelle' => 'Contacts',
            'prefix' => 'societe',
            'route' => 'societe',
            'route_etablissement' => 'etablissement_visualisation',
            'etablissement' => $etablissement,
            'target' => '_self'
       ));
	endif;
?>
<?php if ($sf_user->hasCredential(Roles::TELEDECLARATION)): ?>
<?php if ($sf_user->hasCredential(Roles::TELEDECLARATION_VRAC)):
  include_component('common', 'navItem', array(
         'libelle' => 'Contrats',
         'prefix' => 'vrac',
         'route' => 'vrac_societe',
         'teledeclaration' => true,
         'identifiant' => $sf_user->getCompte()->getSociete()->getEtablissementPrincipal()->identifiant,
         'target' => '_self'
  ));
 endif; ?>
<?php if ($sf_user->hasCredential(Roles::TELEDECLARATION_DRM)):
  include_component('common', 'navItem', array(
       'libelle' => 'DRM',
       'prefix' => 'drm',
       'route' => 'drm_societe',
       'teledeclaration' => true,
       'identifiant' => $sf_user->getCompte()->getSociete()->getEtablissementPrincipal()->identifiant,
       'target' => '_self'
));
endif; ?>
<?php endif; ?>
<?php include_partial('common/navExtra'); ?>
</ul>
<ul class="nav navbar-nav navbar-right">
<?php if ($sf_user->hasCredential('transactions') || $sf_user->hasCredential('contacts')) : ?>
        <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-search"></span><span class="caret"></span></a>
          <ul class="dropdown-menu">
            <?php if ($sf_user->hasCredential('transactions')): ?>
            <li><a href="<?php echo url_for("statistiques_vrac") ?>">Contrat d'achat</a></li>
            <li><a href="<?php echo url_for("statistiques_drm") ?>">DRM</a></li>
            <?php endif; ?>
            <?php if ($sf_user->hasCredential('contacts')): ?>
            <li><a href="<?php echo url_for("societe") ?>">Contacts</a></li>
            <?php endif; ?>
          </ul>
        </li>
<?php endif; ?>
<?php if ($sf_user->hasCredential('transactions') && StatistiqueConfiguration::getInstance()->isActif()): ?>
    <li><a tabindex="-1" href="<?php echo url_for("statistiques_stats") ?>"><span class="glyphicon glyphicon-stats"></span></a></li>
<?php endif; ?>
<?php if ($sf_user->hasCredential('admin')) : ?>
        <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-cog"></span><span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo url_for("produits") ?>">Catalogue produit</a></li>
            <li><a href="<?php echo url_for("comptabilite_edition") ?>">Codes analytiques</a></li>
          </ul>
        </li>
<?php if ($etablissement): ?>
<?php if (preg_match('/drm/', $module)) : ?>
     <li><a tabindex="-1" href="<?php echo url_for('drm_debrayage', array('identifiant' => $etablissement->identifiant)) ?>"><span class="glyphicon glyphicon-cloud-upload"></span></a></li>
<?php elseif (preg_match('/vrac/', $module)): ?>
     <li><a tabindex="-1" href="<?php echo url_for('vrac_debrayage', array('identifiant' => $etablissement->identifiant)) ?>"><span class="glyphicon glyphicon-cloud-upload"></span></a></li>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php if ($sf_user->hasCredential(Roles::TELEDECLARATION)): ?>
     <li><a tabindex="-1" href="<?php echo url_for("compte_teledeclarant_modification") ?>"><span class="glyphicon glyphicon-user"></span></a></li>
<?php endif; ?>
<?php if ($sf_user->isAuthenticated()): ?>
<?php if ($sf_user->isUsurpationCompte()): ?>
     <li><a tabindex="-1" href="<?php echo url_for('auth_deconnexion_usurpation') ?>"><span class="glyphicon glyphicon-cloud-download"></span></a></li>
<?php else: ?>
     <li><a tabindex="-1" href="<?php echo url_for('auth_logout') ?>"><span class="glyphicon glyphicon-log-out"></span></a></li>
<?php endif; ?>
<?php else: ?>
     <li><a tabindex="-1" href="<?php echo url_for('common_homepage') ?>">Connexion</a></li>
<?php endif; ?>
</ul>
</div>

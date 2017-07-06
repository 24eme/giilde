<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <link rel="shortcut icon" href="/favicon.ico" />
        <?php include_stylesheets() ?>
        <!-- Bootstrap core CSS -->
        <?php if(file_exists(sfConfig::get('sf_web_dir')."/css/bootstrap_".sfConfig::get('sf_app').'.css')): ?>
            <link href="/css/bootstrap_<?php echo sfConfig::get('sf_app'); ?>.css?20161004" rel="stylesheet">
        <?php else: ?>
            <link href="/css/bootstrap.css?20161004" rel="stylesheet">
        <?php endif; ?>
        <link href="/components/select2/select2.css" rel="stylesheet">
        <link href="/components/select2/select2-bootstrap.min.css" rel="stylesheet">
        <link href="/components/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
        <link href="/components/vins/vins.css" rel="stylesheet">
        <link href="/css/style.css?20170324" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script src="/components/jquery/jquery.js"></script>
    </head>
    <body>
        <?php include_partial('common/header'); ?>

        <div id="content" style="min-height: 945px; padding-bottom: 20px;">
            <?php if(sfConfig::get('app_instance') == 'preprod' ): ?>
              <div><p style="color:red; text-align:center; font-weight: bold;">Preproduction (la base est succeptible d'être supprimée à tout moment)</p></div>
            <?php endif; ?>
    		<div class="container">
            <?php echo $sf_content ?>
            </div>

            <div id="ajax-modal" class="modal"></div>
            <?php include_partial('common/footer'); ?>
        </div>

        <script src="/components/bootstrap/bootstrap.js"></script>
        <script src="/components/select2/select2.min.js"></script>
        <script src="/components/moment/moment-with-locales.min.js"></script>
        <script src="/components/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
        <script src="/js/plugins/jquery.plugins.min.js"></script>
        <script src="/js/lib/jquery-ui-1.8.21.min.js"></script>
        <script src="/js/ajaxHelper.js"></script>
        <script src="/js/form.js"></script>
        <script src="/js/colonnes.js"></script>
        <script src="/js/main.js?20170223"></script>
        <script src="/js/teledeclaration.js"></script>
        <script src="/js/conditionnement.js"></script>
        <script src="/js/vrac.js"></script>
        <script src="/js/drm.js?20170706"></script>
        <script src="/js/contacts.js"></script>
        <script src="/js/facture.js"></script>
        <script src="/js/stats.js?20170222"></script>
        <script src="/js/lib/jquery.sticky.js"></script>
  </body>
</html>

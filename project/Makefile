all: web/css/bootstrap_ivbd.css web/css/bootstrap_ivso.css

web/css/bootstrap_ivso.css web/css/bootstrap_ivbd.css: web/less/alerts.less web/less/carousel.less web/less/fonts.less web/less/jumbotron.less web/less/mixins.less web/less/pagination.less web/less/responsive-utilities.less web/less/type.less web/less/badges.less web/less/close.less web/less/forms.less web/less/labels.less web/less/modals.less web/less/panels.less web/less/scaffolding.less web/less/utilities.less web/less/bootstrap.less web/less/code.less web/less/glyphicons.less web/less/navbar.less web/less/popovers.less web/less/tables.less web/less/variables_ivbd.less web/less/variables_ivso.less web/less/breadcrumbs.less web/less/grid.less web/less/list-group.less web/less/navs.less web/less/print.less web/less/theme.less web/less/wells.less web/less/button-groups.less web/less/component-animations.less web/less/input-groups.less web/less/media.less web/less/normalize.less web/less/progress-bars.less web/less/thumbnails.less web/less/buttons.less web/less/dropdowns.less web/less/ivbd-theme.less web/less/ivso-theme.less web/less/pager.less web/less/responsive-embed.less web/less/tooltip.less
	lessc web/less/bootstrap_ivbd.less > web/css/bootstrap_ivbd.css
	lessc web/less/bootstrap_ivso.less > web/css/bootstrap_ivso.css

clean:
	rm web/css/bootstrap_ivbd.css web/css/bootstrap_ivso.css

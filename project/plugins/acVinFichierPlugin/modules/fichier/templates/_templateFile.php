<?php $ligneId = "ligne_" . str_replace(array("[", "]"), array("-", ""), $form->renderName()) ?>
<script id="template_file" class="template_details" type="text/x-jquery-tmpl">
<div class="form-group">
	<div class="col-xs-8 col-xs-offset-4">
		<?php echo $form['file']->renderError() ?>
	</div>
	<div class="col-xs-3 col-xs-offset-1">
		<?php echo $form['file']->renderLabel() ?>
	</div>
	<div class="col-xs-6">
		<?php echo $form['file']->render() ?>
	</div>
	<div class="col-xs-2">
		<a type="button" data-line="#<?php echo $ligneId ?>" data-add="#files_adder" data-lines="#files_wrapper div" tabindex="-1" class="btn btn-xs btn-danger dynamic-element-delete"><span class="glyphicon glyphicon-remove"></span></a>
	</div>
</div>
</script>
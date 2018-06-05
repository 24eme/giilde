<?php
use_helper('Asset');
$interpro = sfConfig::get('app_teledeclaration_interpro');
?>
<div style="text-align: center; top: 30%; position: absolute; width: 99%;"><center>
<img src="<?php echo _compute_public_path('images/'. $interpro . '2douane.gif', null, '.gif') ?>" width="800" height="150"/>
<p>Transmission des données à pro.douane.gouv.fr en cours... <br/>Veuillez patienter</p>
</center></div>
<form id="form_transmission" method="post" action="<?php echo url_for('drm_ciel', $drm); ?>">
</form>
<script src="<?php echo _compute_public_path('js/lib/jquery-1.7.2.min.js', null, 'js') ?>"></script>
<script type="text/javascript">
        $(document).ready(function() {
           setTimeout("$('#form_transmission').submit();", 1000);
        });
</script>

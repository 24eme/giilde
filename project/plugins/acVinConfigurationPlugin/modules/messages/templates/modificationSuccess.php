<ol class="breadcrumb">
    <li>
        <a href="<?php echo url_for("produits") ?>#messages">Messages</a>
    </li>
    <li class="active">
        <a href="#"><?php echo $messageId; ?></a>
    </li>
</ol>

<h2>Modification du message</h2>
<form style="margin-top: 20px;" action="" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <div class="form-group">
        <?php echo $form['message']->renderLabel(null, array('class' => "col-sm-2 control-label")); ?>
        <div class="col-sm-10">
            <?php echo $form['message']->render(); ?>
        </div>
    </div>

    <div id="previsulisation_message" class="alert alert-info">
    </div>

    <div class="form-group">
        <div class="col-sm-6">
            <a href="<?php echo url_for('produits') ?>#messages" class="btn btn-default">Annuler</a>
        </div>
       <div class="col-sm-6 text-right">
           <button type="submit" class="btn btn-default">Valider</button>
       </div>
   </div>
</form>

<script type="text/javascript">
    function updatePrevisualisation() {
        document.getElementById('previsulisation_message').innerHTML =
            "<span class=\"glyphicon glyphicon-info-sign\"></span> " + document.getElementById('messages_message').value.replace(/\n/g, "<br />");
    }

    updatePrevisualisation();
    document.getElementById('messages_message').onkeyup = function() {
        updatePrevisualisation();
    };
</script>

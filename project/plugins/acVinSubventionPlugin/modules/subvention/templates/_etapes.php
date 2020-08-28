<nav class="navbar navbar-default ">
    <ul class="nav navbar-nav">
        <li class="<?php if($sf_request->getParameter('action') == 'infos'): ?>active<?php endif; ?>"><a href="<?php echo url_for("subvention_infos", $subvention) ?>"><span>Identification</span></a></li>
        <li class="<?php if($sf_request->getParameter('action') == 'dossier'): ?>active<?php endif; ?>"><a href="<?php echo url_for("subvention_dossier", $subvention) ?>"><span>Actions menées</span></a></li>
        <li class="<?php if($sf_request->getParameter('action') == 'engagements'): ?>active<?php endif; ?>"><a href="<?php echo url_for("subvention_engagements", $subvention) ?>"><span>Engagements</span></a></li>
        <li class="<?php if($sf_request->getParameter('action') == 'validation'): ?>active<?php endif; ?>"><a href="<?php echo url_for("subvention_validation", $subvention) ?>"><span>Validation</span></a></li>
    </ul>
</nav>

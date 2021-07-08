<nav class="navbar navbar-default ">
    <ul class="nav navbar-nav">
        <li class="<?php if($sf_request->getParameter('action') == 'infos'): ?>active<?php endif; ?>"><a href="<?php echo url_for("dsnegoce_infos", $dsnegoce) ?>"><span>Identification</span></a></li>
          <li class="<?php if($sf_request->getParameter('action') == 'stocks'): ?>active<?php endif; ?>"><a href="<?php echo url_for("dsnegoce_stocks", $dsnegoce) ?>"><span>Stocks</span></a></li>
        <li class="<?php if($sf_request->getParameter('action') == 'validation'): ?>active<?php endif; ?>"><a href="<?php echo url_for("dsnegoce_validation", $dsnegoce) ?>"><span>Validation</span></a></li>
    </ul>
</nav>

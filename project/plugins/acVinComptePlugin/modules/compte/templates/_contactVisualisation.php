
<div class="<?php if (isset($smallBlock)): ?>col-xs-12 <?php else: ?>col-xs-6 <?php endif; ?> <?php if (isset($smallBlock)): ?>text-left<?php endif; ?>">
    <?php if ($compte->email) : ?>
        <div class="row">
            <div class="col-xs-3">
                <strong>Email <?php if ($compte->isSocieteContact()) { echo " compta"; } ?> :</strong>
            </div>
            <div class="col-xs-9">
                <a href="mailto:<?php echo $compte->email; ?>"><?php echo $compte->email; ?></a>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($compte->telephone_perso) : ?>
        <div class="row">
            <div class="col-xs-3">
                <strong>Tél. perso : </strong>
            </div>
            <div class="col-xs-9">
                <a href="callto:<?php echo $compte->telephone_perso; ?>"><?php echo $compte->telephone_perso; ?></a>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($compte->telephone_bureau) : ?>
        <div class="row">
            <div class="col-xs-3">
                <strong>Tél. bureau : </strong>
            </div>
            <div class="col-xs-9"><a href="callto:<?php echo $compte->telephone_bureau; ?>"><?php echo $compte->telephone_bureau; ?></a>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($compte->telephone_mobile) : ?>
        <div class="row">
            <div class="col-xs-3">
                <strong>Tél. mobile : </strong>
            </div>
            <div class="col-xs-9">
                <a href="callto:<?php echo $compte->telephone_mobile; ?>"><?php echo $compte->telephone_mobile; ?></a>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($compte->fax) : ?>
        <div class="row">
            <div class="col-xs-3">
                <strong>Fax : </strong>
            </div>
            <div class="col-xs-9">
                <a href="callto:<?php echo $compte->fax; ?>"><?php echo $compte->fax; ?></a>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($compte->exist('site_internet') && $compte->site_internet) : ?>
        <div class="row">
            <div class="col-xs-3">
                <strong>Site Internet : </strong>
            </div>
            <div class="col-xs-9">
                <a href="<?php echo $compte->site_internet; ?>"><?php echo $compte->site_internet; ?></a>
            </div>
        </div>
    <?php endif; ?>
    <?php if(!$compte->getTelephoneBureau() && !$compte->getEmail() && !$compte->getFax() && !$compte->getTelephonePerso() && !$compte->getTelephoneMobile() && !$compte->getSiteInternet()): ?>
        <div class="row">
            <div class="text-center text-muted col-xs-12">
                <em>Aucune information de contact</em>
            </div>
        </div>
    <?php endif; ?>
</div>

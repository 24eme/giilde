<?php

$xmlFile = dirname(__FILE__)."/xml/".str_replace("/", "", $_GET['file']).".xml";

preg_match('/^(.+)_(.+)_(.+)_(.+)/', $_GET['file'], $matches);

$date = new DateTime(preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '\1-\2-\3 \4:\5:\6', $matches[1]));
$application = $matches[2];
$commit = $matches[3];
$branch = $matches[4];

$xml = new SimpleXMLElement(file_get_contents($xmlFile));

?>
<!doctype html>
<html lang="fr_FR">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <title>Test <?php echo $application; ?> <?php echo $commit; ?></title>
</head>
<body>
    <div class="container" style="margin-top: 20px;">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/statuts/tests.php">Tests</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="">Visualisation du test <small>(<?php echo substr($commit, 0, 10) ?>)</small></a></li>
          </ol>
        </nav>

        <a class="float-right btn btn-sm btn-link" href="./xml/<?php echo $_GET['file'] ?>.xml" class="btn btn-link btn-sm">Voir le fichier xml</a>
        <h2>Visualisation du Test <small class="text-muted">(<?php echo substr($commit, 0, 10) ?>)</small></h2>
        <p>
            Date : <?php echo $date->format('d/m/Y H:i') ?><br />
            Application : <?php echo $application ?><br />
            Commit : <?php echo $commit ?><br />
            Branche : <?php echo $branch ?><br />
       </p>
       <?php $hasError = false; ?>
            <?php foreach($xml as $suite): ?>
                <?php if($suite['errors'] == 0 && $suite['failures'] == 0): continue; endif; ?>
                <?php $hasError = true; ?>
                <div class="alert alert-danger">
                    <h6><strong><?php echo $suite['name']; ?></strong>
                    <?php $nbErrors = $suite['failures']*1 + $suite['assertions']*1 - count($suite); ?>
                    <?php if($nbErrors > 0): ?>
                    <small class="badge badge-pill badge-danger"><?php echo $nbErrors ?> échec<?php if($nbErrors > 1): ?>s<?php endif; ?></small></h6>
                    <?php endif; ?>
                <?php foreach($suite as $item): ?>
                    <?php if(!count($item)): continue; endif; ?>
                    <div style="margin-top: 10px;">
                    <?php echo $item['name']; ?> <small><?php echo $item['file']; ?>:<?php echo $item['line']; ?></small><br />
                        <?php foreach($item as $error): ?>
                            <samp><small><?php echo $error; ?></small></samp>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                </div>
                <?php endforeach; ?>

        <?php if(!$hasError): ?>
        <div class="alert alert-success">Tous les tests passent avec succès</div>
        <?php endif; ?>
    </div>

</body>
</html>

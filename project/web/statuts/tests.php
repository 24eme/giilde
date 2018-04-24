<?php

$directory = dirname(__FILE__);
$files = scandir($directory."/xml");

$tests = array();

foreach($files as $file) {
    if(!preg_match('/^(.+)_(.+)_(.+)_(.+)\.xml/', $file, $matches)) {
        continue;
    }
    $xml = new SimpleXMLElement(file_get_contents($directory."/xml/".$file));

    $date = preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$/', '\1-\2-\3 \4:\5:\6', $matches[1]);
    $application = $matches[2];
    $commit = $matches[3];
    $branch = $matches[4];

    $test = new stdClass();
    $test->date = new DateTime($date);
    $test->application = $application;
    $test->commit = $commit;
    $test->branch = $branch;
    $test->file = $file;
    $test->success = (!($xml['failures']*1) && !($xml['errors']*1));
    $test->nb = $xml['tests']*1;

    $tests[$test->date->format('YmdHis')] = $test;
}

krsort($tests);

?>
<!doctype html>
<html lang="fr_FR">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Tests</title>
</head>
<body>
    <div class="container" style="margin-top: 20px;">
        <h2>Tests <img src="/statuts/tests.svg.php" /></h2>
        <table style="margin-top: 20px;" class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th class="col-xs-4">Date</th>
                    <th class="col-xs-4">Projet</th>
                    <th class="col-xs-4">Branche</th>
                    <th class="col-xs-4">Commit</th>
                    <th class="col-xs-4">NB Tests</th>
                    <th class="col-xs-4">État</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($tests as $test): ?>
                <tr class="">
                    <td><?php echo $test->date->format('d/m/Y H:i'); ?></td>
                    <td><?php echo $test->application; ?></td>
                    <td><?php echo $test->branch; ?></td>
                    <td><?php echo $test->commit; ?></td>
                    <td class="text-center"><?php echo $test->nb; ?></td>
                    <td class="<?php if($test->success): ?>text-success<?php else: ?>text-danger<?php endif; ?>"><?php if($test->success): ?>Succès<?php else: ?>Échec<?php endif; ?></td>
                    <td><a href="/statuts/xml/<?php echo $test->file ?>">Voir</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>

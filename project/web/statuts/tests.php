<?php

$directory = dirname(__FILE__);
$files = scandir($directory."/xml");

sort($files);

$output = 'html';

if(isset($_GET['format']) && $_GET['format']) {
    $output = $_GET['format'];
}

$tests = array();
$precs = array();
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
    $test->nb = $xml['tests']*1;
    $test->nb_errors = 0;
    foreach($xml as $item) {
        if(!$item["errors"]*1) {
            continue;
        }
        $test->nb_errors += $item['failures']*1 + $item['assertions']*1 - count($item);
    }
    $test->success = !$test->nb_errors;
    $test->nb_success = $test->nb - $test->nb_errors;
    $test->diff_nb_success = 0;
    $test->diff_nb_errors = 0;

    $precTest = null;
    if(isset($prec[$test->application.'_'.$test->branch]) && $prec[$test->application.'_'.$test->branch] && $prec[$test->application.'_'.$test->branch]->commit != $test->commit) {
        $precTest = $prec[$test->application.'_'.$test->branch];
    }

    if($precTest) {
        $test->diff_nb_errors = $test->nb_errors - $precTest->nb_errors;
        $test->diff_nb_success = $test->nb_success - $precTest->nb_success;
    }

    $prec[$test->application.'_'.$test->branch] = $test;
    $tests[$test->date->format('YmdHis')] = $test;
}

krsort($tests);

?>
<?php if($output == "xml"): ?>
<?php header('Content-Type: text/xml'); ?>
<?xml version="1.0" encoding="utf-8"?>
    <feed xmlns="http://www.w3.org/2005/Atom">
    	<title>Tests <?php echo $application ?></title>
    	<updated><?php echo current($tests)->date->format('Y-m-d H:i:s') ?></updated>

        <?php foreach($tests as $test): ?>
        <?php if(!$test->diff_nb_success && !$test->diff_nb_errors): continue; endif;?>
        <entry>
    		<title>Le bilan des tests a évolué : <?php echo $test->nb_success ?> (<?php if($test->diff_nb_success > 0): ?>+<?php endif; ?><?php echo $test->diff_nb_success ?>) SUCCESS / <?php echo $test->nb_errors ?> (<?php if($test->diff_nb_errors > 0): ?>+<?php endif; ?><?php echo $test->diff_nb_errors ?>) FAILED </title>
    	    <id><?php echo $test->commit ?></id>
    	    <link><?php echo (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].preg_replace("/\?.+$/", "", $_SERVER['REQUEST_URI']) ?>?file=<?php echo str_replace('.xml', '', $test->file) ?></link>
    		<updated><?php echo $test->date->format('Y-m-d H:i:s') ?></updated>
    	</entry>
        <?php endforeach; ?>
    </feed>
<?php exit; ?>
<?php endif; ?>
<!doctype html>
<html lang="fr_FR">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <title>Tests</title>
</head>
<body>
    <div class="container" style="margin-top: 20px;">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/statuts/tests.php">Tests</a></li>
          </ol>
        </nav>
        <a class="float-right btn btn-sm btn-link" href="tests.php?format=xml">Feed </a>
        <h2>Tests <img src="./tests.svg.php" /></h2>
        <table style="margin-top: 20px;" class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th class="col-xs-3">Date</th>
                    <th class="col-xs-2">Projet</th>
                    <th class="col-xs-2">Branche</th>
                    <th class="col-xs-3">Commit</th>
                    <th class="col-xs-2">NB Tests</th>
                    <th class="col-xs-2">NB Erreurs</th>
                    <th class="col-xs-2">État</th>
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
                    <td class="text-center <?php if($test->success): ?>text-success<?php else: ?>text-danger<?php endif; ?>"><?php echo $test->nb_errors ?></td>
                    <td class="<?php if($test->success): ?>text-success<?php else: ?>text-danger<?php endif; ?>"><?php if($test->success): ?>Succès<?php else: ?>Échec<?php endif ?></td>
                    <td><a href="tests_view.php?file=<?php echo str_replace('.xml', '', $test->file) ?>">Voir</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>

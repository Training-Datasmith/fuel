<?php
/**
 * App test bootstrap: core PHPUnit bootstrap plus shared test helpers.
 */

require dirname(dirname(__DIR__)).'/core/bootstrap_phpunit.php';

$clusterDb = APPPATH.'tmp'.DIRECTORY_SEPARATOR.'cluster.sqlite';
if (is_file($clusterDb))
{
	unlink($clusterDb);
}

require APPPATH.'tests/support/Subprocess.php';
require APPPATH.'tests/support/RequestTestCase.php';
require APPPATH.'tests/support/ClusterTestCase.php';
require APPPATH.'tests/support/ClusterSqliteAuthSchema.php';

\Autoloader::add_classes(array(
	'Parser\\View_Markdown' => APPPATH.'classes/parser/view/markdown.php',
));

<?php
/**
 * App test bootstrap: core PHPUnit bootstrap plus shared test helpers.
 */

require dirname(dirname(__DIR__)).'/core/bootstrap_phpunit.php';

require APPPATH.'tests/support/Subprocess.php';
require APPPATH.'tests/support/RequestTestCase.php';

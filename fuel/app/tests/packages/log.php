<?php

use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LogLevel;

#[Group('Packages')]
#[Group('PsrLog')]
class Tests_Packages_Log extends ClusterTestCase
{
	public function test_monolog_logger_writes_psr_log_levels()
	{
		\Log::info('cluster-info-marker');
		$logger = \Log::instance();
		$this->assertInstanceOf(\Psr\Log\LoggerInterface::class, $logger);
		$logger->log(LogLevel::ERROR, 'cluster-psr-marker');

		$log = $this->readApplicationLogContents();
		$this->assertStringContainsString('cluster-info-marker', $log);
		$this->assertStringContainsString('cluster-psr-marker', $log);

		$this->assertIncludedPackageFile('fuel/vendor/psr/log/src/LoggerInterface.php');
		$this->assertIncludedPackageFile('fuel/vendor/psr/log/src/LogLevel.php');
	}
}

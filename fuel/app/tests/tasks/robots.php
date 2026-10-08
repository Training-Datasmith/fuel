<?php
/**
 * Robots oil task tests.
 *
 * @group App
 * @group Robots
 */
class Tests_Robots extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		require_once APPPATH.'tasks/robots.php';
		\Cli::$nocolor = true;
	}

	protected function tearDown(): void
	{
		\Cli::$nocolor = false;
		parent::tearDown();
	}

	public function test_run_default_speech()
	{
		$output = \Fuel\Tasks\Robots::run();
		$this->assertIsString($output);
		$this->assertStringContainsString('"KILL ALL HUMANS!"', $output);
		$this->assertStringContainsString('HHHHH', $output);
	}

	public function test_run_custom_speech()
	{
		$output = \Fuel\Tasks\Robots::run('Kill all Mice');
		$this->assertStringContainsString('"Kill all Mice"', $output);
		$this->assertStringNotContainsString('KILL ALL HUMANS', $output);
	}

	public function test_run_empty_string_is_kept()
	{
		$output = \Fuel\Tasks\Robots::run('');
		$this->assertStringContainsString('""', $output);
		$this->assertStringNotContainsString('KILL ALL HUMANS', $output);
	}

	public function test_protect_speech()
	{
		$output = \Fuel\Tasks\Robots::protect();
		$this->assertStringContainsString('"PROTECT ALL HUMANS"', $output);
		$this->assertStringNotContainsString('KILL ALL HUMANS', $output);
	}

	public function test_oil_discover_robots_task()
	{
		$result = Subprocess::run(
			array(PHP_BINARY, 'oil', 'r', 'robots'),
			array(
				'cwd' => DOCROOT,
				'timeout' => 30,
			)
		);

		$this->assertFalse($result['timed_out']);
		$this->assertSame(0, $result['exit_code']);
		Subprocess::assertCleanStreams($result['stdout'], $result['stderr']);
		$this->assertStringContainsString('KILL ALL HUMANS', $result['stdout']);
	}

	public function test_oil_discover_robots_protect_task()
	{
		$result = Subprocess::run(
			array(PHP_BINARY, 'oil', 'r', 'robots:protect'),
			array(
				'cwd' => DOCROOT,
				'timeout' => 30,
			)
		);

		$this->assertFalse($result['timed_out']);
		$this->assertSame(0, $result['exit_code']);
		Subprocess::assertCleanStreams($result['stdout'], $result['stderr']);
		$this->assertStringContainsString('PROTECT ALL HUMANS', $result['stdout']);
	}
}

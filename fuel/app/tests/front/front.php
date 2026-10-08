<?php
/**
 * Front controller subprocess tests.
 *
 * @group App
 * @group Front
 */
class Tests_Front extends TestCase
{
	public function test_front_root_replaces_profiler_placeholders()
	{
		$result = Subprocess::run(
			array(PHP_BINARY, 'index.php'),
			array(
				'cwd' => DOCROOT.'public',
				'timeout' => 30,
			)
		);

		$this->assertFalse($result['timed_out']);
		$this->assertSame(0, $result['exit_code']);
		Subprocess::assertCleanStreams($result['stdout'], $result['stderr']);
		$this->assertStringContainsString('Welcome!', $result['stdout']);
		$this->assertStringContainsString('successfully installed', $result['stdout']);
		$this->assertStringNotContainsString('{exec_time}', $result['stdout']);
		$this->assertStringNotContainsString('{mem_usage}', $result['stdout']);
		$this->assertSame(1, preg_match('/Page rendered in \d+\.\d+s using \d+\.\d+mb of memory/', $result['stdout']));
	}

	public function test_front_hello_uri()
	{
		$result = Subprocess::run(
			array(PHP_BINARY, 'index.php', '--uri=/hello/Ada'),
			array(
				'cwd' => DOCROOT.'public',
				'timeout' => 30,
			)
		);

		$this->assertFalse($result['timed_out']);
		$this->assertSame(0, $result['exit_code']);
		Subprocess::assertCleanStreams($result['stdout'], $result['stderr']);

		$matches = array();
		$this->assertSame(1, preg_match('/<title>(.*?)<\/title>/s', $result['stdout'], $matches));
		$this->assertSame(
			'Hello, Ada',
			html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')
		);
		$this->assertStringNotContainsString('{exec_time}', $result['stdout']);
	}

	public function test_front_unknown_uri_renders_404()
	{
		$result = Subprocess::run(
			array(PHP_BINARY, 'index.php', '--uri=/missing'),
			array(
				'cwd' => DOCROOT.'public',
				'timeout' => 30,
			)
		);

		$this->assertFalse($result['timed_out']);
		$this->assertSame(0, $result['exit_code']);
		Subprocess::assertCleanStreams($result['stdout'], $result['stderr']);
		$this->assertStringContainsString("We can't find that!", $result['stdout']);
		$this->assertStringNotContainsString('{exec_time}', $result['stdout']);

		$matches = array();
		$this->assertSame(1, preg_match('/<title>(.*?)<\/title>/s', $result['stdout'], $matches));
		$this->assertTrue(in_array($matches[1], array(
			'Aw, crap!',
			'Bloody Hell!',
			'Uh Oh!',
			'Nope, not here.',
			'Huh?',
		), true));
	}
}

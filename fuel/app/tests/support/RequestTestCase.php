<?php
/**
 * Shared setup for in-process Fuel request tests.
 */

abstract class RequestTestCase extends TestCase
{
	/** @var string */
	protected $previousCwd;

	/** @var bool */
	protected $previousCliNocolor;

	protected function setUp(): void
	{
		parent::setUp();
		$this->previousCwd = getcwd();
		chdir(DOCROOT.'public');
		$this->previousCliNocolor = \Cli::$nocolor;
		\Request::reset_request(true);
	}

	protected function tearDown(): void
	{
		\Request::reset_request(true);
		\Cli::$nocolor = $this->previousCliNocolor;
		if ($this->previousCwd !== false) {
			chdir($this->previousCwd);
		}
		parent::tearDown();
	}

	/**
	 * @return string
	 */
	protected function executeResponseBody($uri)
	{
		\Request::reset_request(true);
		$response = \Request::forge($uri)->execute()->response();
		\Request::reset_request(true);

		return (string) $response->body();
	}

	/**
	 * @return \Response
	 */
	protected function executeResponse($uri)
	{
		\Request::reset_request(true);
		$response = \Request::forge($uri)->execute()->response();
		\Request::reset_request(true);

		return $response;
	}

	/**
	 * @param string $html
	 * @return string|null
	 */
	protected function extractTitle($html)
	{
		$matches = array();
		$this->assertSame(1, preg_match('/<title>(.*?)<\/title>/s', $html, $matches));

		return $matches[1];
	}

	/**
	 * @param string $html
	 * @return string|null
	 */
	protected function extractH1Text($html)
	{
		$matches = array();
		$this->assertSame(1, preg_match('/<h1>(.*?)<\/h1>/s', $html, $matches));

		return trim(preg_replace('/\s+/', ' ', strip_tags($matches[1])));
	}
}

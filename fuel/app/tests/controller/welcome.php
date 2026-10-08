<?php
/**
 * Welcome controller and routing tests.
 *
 * @group App
 * @group Controller
 */
class Tests_Controller_Welcome extends RequestTestCase
{
	public function test_index_returns_welcome_page()
	{
		$response = $this->executeResponse('welcome/index');
		$this->assertSame(200, $response->status);
		$body = (string) $response->body();
		$this->assertStringContainsString('Welcome!', $body);
		$this->assertStringContainsString('successfully installed', $body);
		$this->assertStringContainsString('classes/controller/welcome.php', $body);
		$this->assertStringContainsString(\Fuel::VERSION, $body);
		$this->assertStringContainsString('{exec_time}', $body);
	}

	public function test_root_route_renders_the_same_welcome_page()
	{
		\Request::reset_request(true);
		$response = \Request::forge()->execute()->response();
		\Request::reset_request(true);
		$this->assertSame(200, $response->status);
		$this->assertSame('welcome/index', \Config::get('routes._root_'));
		$this->assertStringContainsString('Welcome!', (string) $response->body());
	}

	public function test_hello_route_uses_presenter_default_name()
	{
		$response = $this->executeResponse('hello');
		$this->assertSame(200, $response->status);
		$body = (string) $response->body();
		$this->assertSame('Hello, World', $this->extractTitle($body));
		$this->assertStringContainsString('Hello, World!', $body);
		$this->assertStringContainsString('Congratulations, you just used a Presenter!', $body);
	}

	public function test_hello_route_passes_name_segment()
	{
		$response = $this->executeResponse('hello/Ada');
		$this->assertSame(200, $response->status);
		$body = (string) $response->body();
		$this->assertSame('Hello, Ada', $this->extractTitle($body));
		$this->assertStringContainsString('Hello, Ada!', $body);
	}

	public function test_direct_welcome_hello_also_defaults_name()
	{
		$response = $this->executeResponse('welcome/hello');
		$this->assertSame(200, $response->status);
		$this->assertSame('Hello, World', $this->extractTitle((string) $response->body()));
	}

	public function test_hello_name_is_html_encoded_once()
	{
		$body = $this->executeResponseBody('hello/<b>Bob</b>');
		$this->assertSame(
			'Hello, <b>Bob</b>',
			html_entity_decode($this->extractTitle($body), ENT_QUOTES | ENT_HTML5, 'UTF-8')
		);
		$this->assertStringNotContainsString('<b>Bob</b>', $body);

		\Request::reset_request(true);
		$quoted = $this->executeResponseBody("hello/O'Reilly");
		$this->assertSame(
			"Hello, O'Reilly",
			html_entity_decode($this->extractTitle($quoted), ENT_QUOTES | ENT_HTML5, 'UTF-8')
		);
	}

	public function test_404_action_sets_status_and_known_title()
	{
		$response = $this->executeResponse('welcome/404');
		$this->assertSame(404, $response->status);
		$this->assertSame('welcome/404', \Config::get('routes._404_'));
		$body = (string) $response->body();
		$this->assertTrue(in_array($this->extractTitle($body), array(
			'Aw, crap!',
			'Bloody Hell!',
			'Uh Oh!',
			'Nope, not here.',
			'Huh?',
		), true));
		$this->assertStringContainsString("We can't find that!", $body);
	}

	public function test_missing_controller_throws_not_found_exception()
	{
		\Request::reset_request(true);
		try {
			\Request::forge('no/such/page')->execute();
			$this->fail('Expected HttpNotFoundException was not thrown.');
		} catch (\HttpNotFoundException $e) {
			$this->assertInstanceOf('HttpNotFoundException', $e);
		}
		\Request::reset_request(true);
	}
}

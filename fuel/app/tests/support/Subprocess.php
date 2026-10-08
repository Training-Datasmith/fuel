<?php
/**
 * Bounded subprocess runner with concurrent stdout/stderr capture.
 */

class Subprocess
{
	/**
	 * @param array<int, string> $command
	 * @param array<string, mixed> $options cwd, env, timeout (seconds)
	 * @return array{exit_code:int, stdout:string, stderr:string, timed_out:bool}
	 */
	public static function run(array $command, array $options = array())
	{
		$timeout = isset($options['timeout']) ? (int) $options['timeout'] : 30;
		$cwd = isset($options['cwd']) ? $options['cwd'] : null;
		$env = isset($options['env']) ? $options['env'] : array();

		$descriptorSpec = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);

		$baseEnv = array(
			'FUEL_ENV' => 'test',
		);
		foreach ($env as $key => $value) {
			$baseEnv[$key] = $value;
		}

		$procEnv = self::buildChildEnvironment($baseEnv);
		$proc = proc_open($command, $descriptorSpec, $pipes, $cwd, $procEnv);

		if (! is_resource($proc)) {
			throw new \RuntimeException('Failed to start subprocess.');
		}

		fclose($pipes[0]);

		stream_set_blocking($pipes[1], false);
		stream_set_blocking($pipes[2], false);

		$stdout = '';
		$stderr = '';
		$timedOut = false;
		$exitCode = null;
		$deadline = microtime(true) + $timeout;

		while (true) {
			$status = proc_get_status($proc);
			if (! $status['running'] && $exitCode === null) {
				$exitCode = $status['exitcode'];
			}

			$read = array();
			if (! feof($pipes[1])) {
				$read[] = $pipes[1];
			}
			if (! feof($pipes[2])) {
				$read[] = $pipes[2];
			}

			if (empty($read) && ! $status['running']) {
				break;
			}

			$remaining = $deadline - microtime(true);
			if ($remaining <= 0) {
				$timedOut = true;
				proc_terminate($proc, 9);
				break;
			}

			$sec = (int) floor($remaining);
			$usec = (int) round(($remaining - $sec) * 1e6);
			if ($sec === 0 && $usec === 0) {
				$usec = 1000;
			}

			$write = null;
			$except = null;
			if (! empty($read)) {
				$n = @stream_select($read, $write, $except, $sec, $usec);
				if ($n === false) {
					usleep(1000);
					continue;
				}
				if ($n > 0) {
					foreach ($read as $pipe) {
						$chunk = fread($pipe, 8192);
						if ($chunk === false || $chunk === '') {
							continue;
						}
						if ($pipe === $pipes[1]) {
							$stdout .= $chunk;
						} else {
							$stderr .= $chunk;
						}
					}
				}
			} else {
				usleep(10000);
			}
		}

		$stdout .= stream_get_contents($pipes[1]);
		$stderr .= stream_get_contents($pipes[2]);

		fclose($pipes[1]);
		fclose($pipes[2]);

		$closed = proc_close($proc);
		if ($exitCode === null || $exitCode === -1) {
			$exitCode = $closed;
		}
		if ($timedOut) {
			$exitCode = 124;
		}

		return array(
			'exit_code' => $exitCode,
			'stdout' => $stdout,
			'stderr' => $stderr,
			'timed_out' => $timedOut,
		);
	}

	/**
	 * @param array<string, mixed> $overrides
	 * @return array<string, string>
	 */
	protected static function buildChildEnvironment(array $overrides)
	{
		$environment = array();
		foreach ($_ENV as $key => $value) {
			if (is_string($key) && (is_scalar($value) || $value === null)) {
				$environment[$key] = (string) $value;
			}
		}
		foreach (array('PATH', 'HOME', 'LANG', 'LC_ALL', 'TMPDIR', 'TEMP', 'TMP', 'USER') as $name) {
			$value = getenv($name);
			if ($value !== false) {
				$environment[$name] = $value;
			}
		}
		foreach ($overrides as $key => $value) {
			$environment[$key] = (string) $value;
		}

		return self::stringifyEnvironment($environment);
	}

	/**
	 * @param array<string, mixed> $environment
	 * @return array<string, string>
	 */
	protected static function stringifyEnvironment(array $environment)
	{
		$stringEnv = array();
		foreach ($environment as $key => $value) {
			if (! is_string($key)) {
				continue;
			}
			if (is_scalar($value) || $value === null) {
				$stringEnv[$key] = (string) $value;
			}
		}

		return $stringEnv;
	}

	/**
	 * @param array{exit_code:int, stdout:string, stderr:string, timed_out:bool} $result
	 * @param int $expected
	 */
	public static function assertExitCode(array $result, $expected)
	{
		if ($result['exit_code'] === $expected) {
			return;
		}

		throw new \PHPUnit\Framework\AssertionFailedError(sprintf(
			'Subprocess exit code %s does not match expected %s (timed_out=%s). stdout: %s stderr: %s',
			var_export($result['exit_code'], true),
			var_export($expected, true),
			var_export($result['timed_out'], true),
			$result['stdout'],
			$result['stderr']
		));
	}

	public static function assertCleanStreams($stdout, $stderr)
	{
		$combined = $stdout."\n".$stderr;
		$patterns = array(
			'Deprecated',
			'PhpErrorException',
			'Fatal error',
			'<h1>Error!</h1>',
			'Could not find asset',
		);

		foreach ($patterns as $pattern) {
			if (strpos($combined, $pattern) !== false) {
				throw new \PHPUnit\Framework\AssertionFailedError(
					'Unexpected diagnostic in subprocess output: '.$pattern
				);
			}
		}
	}
}

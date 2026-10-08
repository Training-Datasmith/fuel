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

		$procEnv = self::stringifyEnvironment(array_merge($_ENV, $_SERVER, $baseEnv));
		$proc = proc_open($command, $descriptorSpec, $pipes, $cwd, $procEnv);

		if (! is_resource($proc)) {
			throw new \RuntimeException('Failed to start subprocess.');
		}

		fclose($pipes[0]);

		$stdout = '';
		$stderr = '';
		$timedOut = false;

		stream_set_timeout($pipes[1], $timeout);
		stream_set_timeout($pipes[2], $timeout);

		$stdout = stream_get_contents($pipes[1]);
		$stderr = stream_get_contents($pipes[2]);

		$stdoutMeta = stream_get_meta_data($pipes[1]);
		$stderrMeta = stream_get_meta_data($pipes[2]);
		if (! empty($stdoutMeta['timed_out']) || ! empty($stderrMeta['timed_out'])) {
			$timedOut = true;
			proc_terminate($proc, 9);
		}

		fclose($pipes[1]);
		fclose($pipes[2]);

		$exitCode = proc_close($proc);
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
	 * @param string $stdout
	 * @param string $stderr
	 */
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

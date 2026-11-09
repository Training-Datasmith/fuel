<?php
/**
 * Shared helpers for installed cluster package tests.
 */

abstract class ClusterTestCase extends TestCase
{
	/** @var string */
	protected static $clusterDbFile;

	protected static function clusterDbPath()
	{
		return APPPATH.'tmp'.DS.'cluster.sqlite';
	}

	protected static function wipeClusterDatabase()
	{
		$path = static::clusterDbPath();
		if (is_file($path))
		{
			unlink($path);
		}
		try
		{
			\DB::instance()->disconnect();
		}
		catch (\Exception $e)
		{
		}
	}

	protected function assertIncludedPackageFile($pathFragment)
	{
		$found = false;
		foreach (get_included_files() as $file)
		{
			if (strpos($file, $pathFragment) !== false)
			{
				$found = true;
				break;
			}
		}
		$this->assertTrue($found, 'Expected included file matching: '.$pathFragment);
	}

	protected function readApplicationLogContents()
	{
		$path = APPPATH.'logs'.DS.date('Y').DS.date('m').DS.date('d').'.php';
		if ( ! is_file($path))
		{
			return '';
		}

		$contents = file_get_contents($path);
		$contents = preg_replace('/^<\?php.*?\?>\s*/s', '', $contents);

		return $contents;
	}

	protected function resetAuthRuntime()
	{
		$this->setAuthStatic('Auth\\Auth', '_instance', null);
		$this->setAuthStatic('Auth\\Auth', '_instances', array());
		$this->setAuthStatic('Auth\\Auth', '_verified', array());
		$this->setAuthStatic('Auth\\Auth', '_verify_multiple', false);
		$this->setAuthStatic('Auth\\Auth', '_drivers', array(
			'member' => 'group',
			'has_access' => 'acl',
			'has_any_access' => 'acl',
			'has_all_access' => 'acl',
		));

		foreach (array('Auth\\Auth_Login_Driver', 'Auth\\Auth_Group_Driver', 'Auth\\Auth_Acl_Driver') as $class)
		{
			$this->setAuthStatic($class, '_instance', null);
			$this->setAuthStatic($class, '_instances', array());
		}

		\Auth\Auth_Group_Simplegroup::_init();
		\Auth\Auth_Acl_Simpleacl::_init();
	}

	protected function bootAuthDriver($driver)
	{
		$this->resetAuthRuntime();
		\Config::set('auth.driver', $driver);
		\Config::load('auth', true);
		if ($driver === 'Simpleauth')
		{
			\Config::load('simpleauth', true);
			\Auth\Auth_Login_Simpleauth::_init();
		}
		else
		{
			\Config::load('ormauth', true);
			\Auth\Auth_Login_Ormauth::_init();
			\Auth\Auth_Group_Ormgroup::_init();
			\Auth\Auth_Acl_Ormacl::_init();
		}
		$instance = \Auth\Auth::forge(array('driver' => $driver));
		$this->setAuthStatic('Auth\\Auth', '_instance', $instance);
	}

	protected function runAuthMigrations(array $migrationFiles)
	{
		foreach ($migrationFiles as $file)
		{
			$class = $file['class'];
			require_once $file['path'];
			$migration = new $class();
			$migration->up();
		}
	}

	protected function setAuthStatic($class, $property, $value)
	{
		$reflection = new ReflectionProperty($class, $property);
		$reflection->setAccessible(true);
		$reflection->setValue(null, $value);
	}
}

<?php

use PHPUnit\Framework\Attributes\Group;

#[Group('Packages')]
#[Group('Docs')]
class Tests_Packages_Docs extends TestCase
{
	public function test_docs_tree_is_installed_without_executable_bootstrap()
	{
		$path = DOCROOT.'docs'.DS.'classes'.DS.'log.html';
		$this->assertFileExists($path);
		$contents = file_get_contents($path);
		$this->assertStringContainsString('Log - Classes - FuelPHP Documentation', $contents);

		$docs_tree_paths = array();
		$docs_root = DOCROOT.'docs'.DS;
		foreach (get_included_files() as $file)
		{
			if (strpos($file, $docs_root) !== false)
			{
				$docs_tree_paths[] = $file;
			}
		}
		$this->assertSame(array(), $docs_tree_paths, 'Installed docs must not be PHP-included during tests');
	}
}

<?php

use PHPUnit\Framework\Attributes\Group;

#[Group('Packages')]
#[Group('Parser')]
class Tests_Packages_Parser extends TestCase
{
	public function test_markdown_view_renders_heading_and_bold()
	{
		$html = (string) \Parser\View::forge('cluster/note.md', array(), false);
		$this->assertStringContainsString('<h1>Cluster title</h1>', $html);
		$this->assertStringContainsString('<strong>cluster-bold</strong>', $html);
		$this->assertIncludedPackageFile('fuel/packages/parser/classes/view/markdown.php');
	}

	public function test_mustache_view_renders_interpolation()
	{
		$view = \Parser\View::forge('cluster/hello.mustache', array('name' => 'Ada'), false);
		$this->assertSame('Hello Ada', trim((string) $view));
		$this->assertIncludedPackageFile('fuel/packages/parser/classes/view/mustache.php');
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
}

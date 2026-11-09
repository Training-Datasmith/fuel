<?php
/**
 * Test-environment Markdown view: Michelf\\Markdown (1.7) because MarkdownExtra breaks on PHP 8.2 (see dependency-failures.md).
 */

namespace Parser;

class View_Markdown extends \View
{
	protected static $_parser;

	protected function process_file($file_override = false)
	{
		$file = $file_override ?: $this->file_name;
		$contents = file_get_contents($file);

		return static::parser()->text($contents);
	}

	public $extension = 'md';

	public static function parser()
	{
		static $parser = null;
		if (is_null($parser))
		{
			$parser = new \Parsedown();
		}

		return $parser;
	}
}

<?php
namespace JRobo\Tasks\Component;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

class Map extends BaseTask implements TaskInterface
{
	protected $toDir = null;

	/**
	 * Constructor.
	 *
	 * @param $toDir
	 */
	public function __construct($toDir)
	{
		$this->toDir = $toDir;
	}

	/**
	 * Maps all parts of an extension into a Joomla! installation
	 *
	 */
	public function run()
	{
		$toDir = $this->toDir;

		$codeBase = __DIR__ . '/code';

		if ( ! is_dir($codeBase))
		{
			$this->printTaskError('Directory: ' . $codeBase . ' is not available');

			return false;
		}

		$dirHandle = opendir($codeBase);

		if ($dirHandle === false)
		{
			$this->printTaskError('Can not open' . $codeBase . 'for parsing');

			return false;
		}

		// This runs thru all main dirs
		while (false !== ($element = readdir($dirHandle)))
		{
			if ($element != "." && $element != "..")
			{
				if (is_dir($codeBase . '/' . $element))
				{
					$method = 'process' . ucfirst($element);

					if (method_exists($this, $method))
					{
						$this->$method($codeBase, $toDir);
					}
				}
			}
		}

		closedir($dirHandle);

		return true;
	}

	private function processAdministrator($codeBase, $toDir)
	{
		// Component directory
		$this->processComponents($codeBase . '/administrator', $toDir . '/administrator');

		// Languages
		$this->processLanguage($codeBase . '/administrator', $toDir . '/administrator');

		// Modules
		$this->processModules($codeBase . '/administrator', $toDir . '/administrator');
	}

	private function processComponents($codeBase, $toDir)
	{
		$base  = $codeBase . '/components';

		// Component directory
		if (is_dir($base))
		{
			$dirHandle = opendir($base);

			while (false !== ($element = readdir($dirHandle)))
			{
				if (false !== strpos($element, 'com_'))
				{
					$this->symlink($base . '/' . $element, $toDir . '/components/' . $element);
				}
			}
		}
	}

	private function processLanguage($codeBase, $toDir)
	{
		$base  = $codeBase . '/language';

		if (is_dir($base))
		{
			$dirHandle = opendir($base);

			while (false !== ($element = readdir($dirHandle)))
			{
				if ($element != "." && $element != "..")
				{
					if (is_dir($element))
					{
						$langDirHandle = opendir($base . '/' . $element);

						while (false !== ($file = readdir($langDirHandle)))
						{
							if (is_file($file))
							{
								$this->symlink($base . '/' . $element . '/' .$file, $toDir . '/language/' . $element . '/' . $file);
							}
						}
					}
				}
			}
		}
	}

	private function processLibraries($codeBase, $toDir)
	{
		$this->mapDir('libraries', $codeBase, $toDir);
	}

	private function processMedia($codeBase, $toDir)
	{
		$this->mapDir('media', $codeBase, $toDir);
	}

	private function processModules($codeBase, $toDir)
	{
		$this->mapDir('modules', $codeBase, $toDir);
	}

	private function processPlugings($codeBase, $toDir)
	{
		$base  = $codeBase . '/plugins';

		if (is_dir($base))
		{
			$dirHandle = opendir($base);

			while (false !== ($element = readdir($dirHandle)))
			{
				if ($element != "." && $element != "..")
				{
					if (is_dir($element))
					{
						$this->mapDir($element, $base, $toDir . '/plugins');
					}
				}
			}
		}
	}

	private function mapDir($type, $codeBase, $toDir)
	{
		$base  = $codeBase . '/' . $type;

		// Check if dir exists
		if (is_dir($base))
		{
			$dirHandle = opendir($base);

			while (false !== ($element = readdir($dirHandle)))
			{
				if ($element != "." && $element != "..")
				{
					$this->symlink($base . '/' . $element, $toDir . '/' . $type . '/' . $element);
				}
			}
		}
	}

	private function symlink($source, $target)
	{
		$this->say('Source: ' . $source);
		$this->say('Target: ' . $target);

		return;

		if (file_exists($target))
		{
			$this->_deleteDir($target);
		}

		try
		{
			$this->taskFileSystemStack()
				->symlink($source, $target)
				->run();
		}
		catch (Exception $e)
		{
			$this->say('ERROR: ' . $e->message());
		}
	}
}
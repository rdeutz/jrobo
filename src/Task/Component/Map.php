<?php

namespace JRobo\Task\Component;

use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;
use Robo\Task\FileSystem\FilesystemStack;

class Map extends BaseTask implements TaskInterface
{
	protected $toDir    = null;

	protected $codebase = null;

	/**
	 * Constructor.
	 *
	 * @param $formDir
	 * @param $toDir
	 */
	public function __construct($formDir, $toDir)
	{
		$this->toDir    = $toDir;
		$this->codebase = $formDir . '/code';
	}

	/**
	 * Maps all parts of an extension into a Joomla! installation
	 */
	public function run()
	{
		$toDir = $this->toDir;

		$codeBase = $this->codebase;

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

					$this->printTaskInfo('Check:' . $method);

					if (method_exists($this, $method))
					{
						$this->printTaskInfo('Process:' . $method);
						$this->$method($codeBase, $toDir);
					}
				}
			}
		}

		closedir($dirHandle);

		$this->printTaskSuccess("Mapping executed");

		return Result::success($this);
	}

	private function processAdministrator($codeBase, $toDir = null)
	{
		$toDir = $this->getToDir($toDir);

		// Component directory
		$this->processComponents($codeBase . '/administrator', $toDir . '/administrator');

		// Languages
		$this->processLanguage($codeBase . '/administrator', $toDir . '/administrator');

		// Modules
		$this->processModules($codeBase . '/administrator', $toDir . '/administrator');
	}

	private function processComponents($codeBase, $toDir = null)
	{
		$toDir = $this->getToDir($toDir);
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

	private function processLanguage($codeBase, $toDir = null)
	{
		$toDir = $this->getToDir($toDir);
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

	private function processLibraries($codeBase, $toDir = null)
	{
		$this->mapDir('libraries', $codeBase, $toDir);
	}

	private function processMedia($codeBase, $toDir = null)
	{
		$this->mapDir('media', $codeBase, $toDir);
	}

	private function processModules($codeBase, $toDir = null)
	{
		$this->mapDir('modules', $codeBase, $toDir);
	}

	private function processPlugins($codeBase, $toDir = null)
	{
		$toDir = $this->getToDir($toDir);
		$base  = $codeBase . '/plugins';

		if (is_dir($base))
		{
			$dirHandle = opendir($base);

			while (false !== ($element = readdir($dirHandle)))
			{
				if ($element != "." && $element != "..")
				{
					if (is_dir($base . '/' . $element))
					{
						$this->mapDir($element, $base, $toDir . '/plugins');
					}
				}
			}
		}
	}

	private function mapDir($type, $codeBase, $toDir = null)
	{
		$toDir = $this->getToDir($toDir);
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
		$this->printTaskInfo('Source: ' . $source);
		$this->printTaskInfo('Target: ' . $target);

		$fs = new FilesystemStack;

		if (file_exists($target))
		{
			$this->printTaskInfo('Delete Taget:' . $target);
			$fs->remove($target);
		}

		try
		{
			$fs->symlink($source, $target)
				->run();
		}
		catch (\Exception $e)
		{
			$this->printTaskError('ERROR: ' . $e->message());
		}
	}

	private function getToDir($toDir=null)
	{
		if (is_null($toDir))
		{
			$toDir = $this->toDir;
		}

		return $toDir;
	}
}
<?php

namespace JRobo;

use Robo\Common\IO;

/**
 * Class Runner
 * @package  JRobo
 * @since   1.0
 */
class Runner extends \Robo\Runner
{
	use IO;

	const ROBOCLASS = 'JRoboFile';
	const ROBOFILE = 'JRoboFile.php';

	public function __construct()
	{
		$this->roboClass = self::ROBOCLASS;
		$this->roboFile  = self::ROBOFILE;
	}

	/**
	 * created a JRoboFile
	 */
	protected function initRoboFile()
	{
		file_put_contents(
			self::ROBOFILE,
			'<?php'
			. "\n/**"
			. "\n * This is project's console commands configuration for JRobo task runner."
			. "\n *"
			. "\n * @see http://robo.li/"
			. "\n */"
			. "\nclass " . self::ROBOCLASS . " extends \\JRobo\\Tasks\n{\n    // define public methods as commands\n}"
		);
		$this->writeln(self::ROBOFILE . " created");

	}
}

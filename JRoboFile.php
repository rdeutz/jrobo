<?php
/**
 * This is project's console commands configuration for JRobo task runner.
 *
 * @see http://robo.li/
 */
class JRoboFile extends \JRobo\Tasks
{
    public function run()
    {
        $from = __DIR__;
        $to   = __DIR__ . '/test';
        $this->taskMap($from, $to)->run();
    }
}
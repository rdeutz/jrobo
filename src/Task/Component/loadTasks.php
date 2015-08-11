<?php

namespace JRobo\Task\Component;


use JRobo\Tasks\Component\Map;

trait loadTasks
{
    /**
     * @param $dirs
     * @return Map
     */
    protected function taskMap($dir)
    {
        return new Map($dir);
    }

}

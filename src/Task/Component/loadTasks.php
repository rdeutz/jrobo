<?php

namespace JRobo\Task\Component;


use JRobo\Task\Component\Map;

trait loadTasks
{
    /**
     * @param $dirs
     * @return Map
     */
    protected function taskMap($from, $to)
    {
        return new Map($from, $to);
    }

}

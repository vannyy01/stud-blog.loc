<?php

namespace api\models;
class Node
{
    private static $data = [];

    /**
     * @return array
     */
    public static function getData(): array
    {
        return self::$data;
    }

    /**
     * @param $data
     */
    public function setData(array $data)
    {
        self::$data = $data;
    }

    /**
     *
     */
    public function unsetData()
    {
        self::$data = [];
    }
}
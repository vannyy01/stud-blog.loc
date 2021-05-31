<?php
/**
 * Created by PhpStorm.
 * User: vannyy
 * Date: 11.05.18
 * Time: 20:09
 */

namespace api\controllers;


trait Domain
{
    private static function allowedDomains(): array
    {
        return [
            'http://localhost:3030'
        ];
    }
}
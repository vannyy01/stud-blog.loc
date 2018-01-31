<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'csrfParam' => '_csrf-api'
        ],
        'response' => [
            'format' => 'json',
            'formatters' => [
                'json' => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],

        'session' => [
            'name' => 'stud-blog-session',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false, //отключаем преобразование во множественную форму
                    'controller' => 'post',
                    'extraPatterns' => [
                        'GET test' => 'test',
                        'OPTIONS test' => 'options',
                        'GET tags' => 'tags',
                        'OPTIONS tags' => 'options',
                        'GET category' => 'category',
                        'OPTIONS category' => 'options',
                        'OPTIONS create' => 'options'
                    ],
                    'except' => ['index', 'create', 'delete']
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false, //отключаем преобразование во множественную форму
                    'controller' => 'profile',
                    'extraPatterns' => [
                        'POST create' => 'create',
                        'OPTIONS create' => 'options',
                        'GET user' => 'user',
                        'OPTIONS user' => 'options',
                        'PUT PATCH update' => 'update',
                        'OPTIONS update' => 'options',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'pluralize' => false, //отключаем преобразование во множественную форму
                    'controller' => 'blog',
                    'extraPatterns' => [
                        'GET validate' => 'validate',
                        'OPTIONS validate' => 'options',
                        'GET blogs' => 'blogs',
                        'OPTIONS blogs' => 'options',
                        'POST create' => 'create',
                        'OPTIONS create' => 'options',
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];

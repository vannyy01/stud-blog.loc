<?php
declare(strict_types=1);

namespace api\models;

use common\models\Post;

class PostFull extends Post
{
    /**
     * @return array with needing fields
     */
    public function fields(): array
    {
        return [
            'post_id',
            'post_name',
            'short_description',
            'created_at',
            'category',
            'rait',
            'avatar',
            'author' => 'user',
            'blog' => 'blog',
        ];
    }
}

/**
 * Created by PhpStorm.
 * User: vannyy
 * Date: 24.02.18
 * Time: 19:28
 */
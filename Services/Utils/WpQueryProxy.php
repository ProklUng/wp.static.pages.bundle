<?php

namespace Prokl\StaticPageMakerBundle\Services\Utils;

use RuntimeException;
use WP_Post;
use WP_Query;

/**
 * Class WpQueryProxy
 * @package Prokl\StaticPageMakerBundle\Services\Utils
 *
 * @since 25.01.2021
 */
class WpQueryProxy
{
    /**
     * @var WP_Query $wpQuery
     */
    private $wpQuery;

    /**
     * WpQueryProxy constructor.
     *
     * @param WP_Query $wpQuery
     */
    public function __construct(WP_Query $wpQuery)
    {
        $this->wpQuery = $wpQuery;
    }

    /**
     * Запросить один пост по параметрам.
     *
     * @param array $arguments Аргументы запроса.
     *
     * @return WP_Post
     * @throws RuntimeException
     */
    public function querySinglePost(array $arguments) : WP_Post
    {
        $this->wpQuery->query($arguments);

        if ($this->wpQuery->have_posts()) {
            wp_reset_postdata();

            $result = $this->wpQuery->posts[0];
            if (!is_object($result)) {
                throw new RuntimeException('Error type of result query.');
            }

            return $result;
        }

        wp_reset_postdata();

        throw new RuntimeException(
          'Posts not found.'
        );
    }

    /**
     * ACF поля по ID.
     *
     * @param integer $idPost ID поста.
     *
     * @return array
     * @throws RuntimeException
     */
    public function acfFields(int $idPost) : array
    {
        if (!function_exists('get_fields')) {
            throw new RuntimeException(
                'Plugin ACF not activated.'
            );
        }

        return get_fields($idPost);
    }
}

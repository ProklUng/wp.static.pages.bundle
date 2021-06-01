<?php

namespace Prokl\StaticPageMakerBundle\Services\ContextProcessors;

use Prokl\StaticPageMakerBundle\Services\AbstractContextProcessor;
use Prokl\StaticPageMakerBundle\Services\Utils\WpQueryProxy;
use RuntimeException;

/**
 * Class SeoContextProcessor
 * @package Prokl\StaticPageMakerBundle\Services\ContextProcessors
 *
 * @since 23.01.2021
 */
class SeoContextProcessor extends AbstractContextProcessor
{
    /**
     * @var WpQueryProxy $wpQueryProxy Прокси к WP_Query.
     */
    private $wpQueryProxy;

    /**
     * SeoContextProcessor constructor.
     *
     * @param WpQueryProxy $wpQueryProxy Прокси к WP_Query.
     */
    public function __construct(WpQueryProxy $wpQueryProxy)
    {
        $this->wpQueryProxy = $wpQueryProxy;
    }

    /**
     * @inheritDoc
     */
    public function handle(): array
    {
        if (!array_key_exists('url', $this->context) || !$this->context['url']) {
            return $this->context;
        }

        try {
            $arResult = $this->getDataByUrl($this->context['url']);
        } catch (RuntimeException $e) {
            return $this->context;
        }

        $this->context['title'] = $arResult['title'] ?: $this->context['title'];
        $this->context['description'] = $arResult['description'] ?: $this->context['description'];
        $this->context['h1'] = $arResult['h1'] ?: $this->context['h1'];

        return $this->context;
    }

    /**
     * Мета данные по URL.
     *
     * @param string $url URL статической страницы.
     *
     * @return string[]
     * @throws RuntimeException
     */
    private function getDataByUrl(string $url) : array
    {
        $arResult = [
            'title' => '',
            'description' => '',
            'h1' => '',
        ];

        $wpPost = $this->wpQueryProxy->querySinglePost(
            [
                'post_type' => 'static_pages',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'title' => $url,
            ]
        );

        $metaInfo = $this->wpQueryProxy->acfFields($wpPost->ID);

        $arResult['title'] = $metaInfo['page_title'] ?? '';
        $arResult['description'] = $metaInfo['page_description'] ?? '';
        $arResult['h1'] = $metaInfo['page_h1'] ?? '';

        return $arResult;
    }
}

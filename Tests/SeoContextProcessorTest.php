<?php

namespace Prokl\StaticPageMakerBundle\Tests;

use Faker\Factory;
use Faker\Generator;
use Prokl\StaticPageMakerBundle\Services\ContextProcessors\SeoContextProcessor;
use Prokl\StaticPageMakerBundle\Services\Utils\WpQueryProxy;
use Mockery;
use Prokl\WordpressCi\Base\WordpressableTestCase;
use RuntimeException;
use WP_Post;

/**
 * Class SeoContextProcessortTest
 * @package Prokl\StaticPageMakerBundle\Tests
 *
 * @since 24.01.2021
 */
class SeoContextProcessorTest extends WordpressableTestCase
{
    /**
     * @var SeoContextProcessor $testObject
     */
    private $testObject;

    /**
     * @var Generator $faker
     */
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    /**
     * handle(). No url in context.
     *
     * @return void
     */
    public function testHandleNoUrlInContext() : void
    {
        $this->testObject = new SeoContextProcessor(
            $this->getMockWpQueryProxy('', '', '')
        );

        $context = ['test' => 'test'];

        $this->testObject->setContext($context);

        $this->assertSame(
            $context,
            $this->testObject->handle(),
            'Контекст изменился, а не должен.'
        );
    }

    /**
     * handle(). No url in context.
     *
     * @return void
     */
    public function testHandleInvalidUrlInContext() : void
    {
        $mock = Mockery::mock(WpQueryProxy::class)
            ->makePartial();

        $mock->shouldReceive('querySinglePost')
             ->andThrow(new RuntimeException);

        /** @var WpQueryProxy $mock */
        $this->testObject = new SeoContextProcessor(
            $mock
        );

        $context = ['test' => 'test', 'url' => $this->faker->url];

        $this->testObject->setContext($context);

        $this->assertSame(
            $context,
            $this->testObject->handle(),
            'Контекст изменился, а не должен.'
        );
    }

    /**
     * handle(). Meta tags.
     *
     * @return void
     */
    public function testHandleMetaTags() : void
    {
        $title = $this->faker->sentence();
        $description = $this->faker->sentence();
        $h1 = $this->faker->sentence();

        $this->testObject = new SeoContextProcessor(
            $this->getMockWpQueryProxy($title, $description, $h1)
        );

        $context = ['test' => 'test', 'url' => $this->faker->url];

        $this->testObject->setContext($context);

        $result = $this->testObject->handle();

        $this->assertSame(
            $title,
            $result['title'],
            'Title отработан неправильно.'
        );

        $this->assertSame(
            $description,
            $result['description'],
            'Description отработан неправильно.'
        );

        $this->assertSame(
            $h1,
            $result['h1'],
            'H1 отработан неправильно.'
        );
    }

    /**
     * handle(). Meta tags не из базы.
     *
     * @return void
     */
    public function testHandleVoidMetaTags() : void
    {
        $title = $this->faker->sentence();
        $description = $this->faker->sentence();
        $h1 = $this->faker->sentence();

        $this->testObject = new SeoContextProcessor(
            $this->getMockWpQueryProxy('', '', '')
        );

        $context = [
            'title' => $title,
            'description' => $description,
            'h1' => $h1,
            'test' => 'test',
            'url' => $this->faker->url
        ];

        $this->testObject->setContext($context);

        $result = $this->testObject->handle();

        $this->assertSame(
            $title,
            $result['title'],
            'Title отработан неправильно.'
        );

        $this->assertSame(
            $description,
            $result['description'],
            'Description отработан неправильно.'
        );

        $this->assertSame(
            $h1,
            $result['h1'],
            'H1 отработан неправильно.'
        );
    }

    /**
     * Мок WpQueryProxy.
     *
     * @param string $title
     * @param string $description
     * @param string $h1
     *
     * @return mixed
     */
    private function getMockWpQueryProxy(string $title, string $description, string $h1)
    {
        $response = new \StdClass();
        $response->ID = $this->faker->numberBetween(100, 200);

        $wpPost = new WP_Post($response);

        $mock = Mockery::mock(WpQueryProxy::class)
                ->makePartial();

        $mock->shouldReceive('querySinglePost')->andReturn($wpPost);
        $mock->shouldReceive('acfFields')->andReturn([
            'page_title' => $title,
            'page_description' => $description,
            'page_h1' => $h1,
        ])
            ->getMock();

        return $mock;
    }
}

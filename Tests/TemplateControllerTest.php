<?php

namespace Prokl\StaticPageMakerBundle\Tests;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Prokl\StaticPageMakerBundle\Services\TemplateController;
use LogicException;
use PHPUnit\Framework\TestCase;
use Timber\Timber;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class TemplateControllerTest
 * @package Prokl\StaticPageMakerBundle\Tests
 *
 * @since 25.01.2021
 */
class TemplateControllerTest extends TestCase
{
    /**
     * @var TemplateController $testObject
     */
    private $testObject;

    /**
     * @var Generator $faker
     */
    private $faker;

    /**
     * @var array $backupTimberLocations
     */
    private $backupTimberLocations = [];

    /**
     * @throws Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
        $this->backupTimberLocations = Timber::$locations;

        Timber::$locations[] = __DIR__ . '/templates';

        /** @var Environment $twig */
        $twig = container()->get('twig.instance');

        $this->testObject = new TemplateController(
            $twig
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Timber::$locations = $this->backupTimberLocations;
    }

    /**
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function testNoTwigPassed() : void
    {
        $this->testObject = new TemplateController();

        $this->expectException(LogicException::class);

        $this->testObject->templateAction(
          $this->faker->sentence,
        );
    }

    /**
     * Выставляет ли заголовки.
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function testSetHeaders() : void
    {
        $maxAge = $this->faker->numberBetween(200, 400);
        $sharedAge = $this->faker->numberBetween(200, 400);
        $private = true;

        $result = $this->testObject->templateAction(
            './void.twig',
            $maxAge,
            $sharedAge,
            $private
        );

        $this->assertSame(
            (string)$maxAge,
            $result->headers->getCacheControlDirective('max-age')
        );

        $this->assertSame(
            (string)$sharedAge,
            $result->headers->getCacheControlDirective('s-maxage')
        );

        $this->assertTrue(
            $result->headers->getCacheControlDirective('private')
        );
    }
}

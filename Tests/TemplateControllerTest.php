<?php

namespace Prokl\StaticPageMakerBundle\Tests;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Prokl\StaticPageMakerBundle\Services\TemplateController;
use LogicException;
use PHPUnit\Framework\TestCase;
use Prokl\TestingTools\Base\BaseTestCase;
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
class TemplateControllerTest extends BaseTestCase
{
    /**
     * @var TemplateController $testObject
     */
    private $testObject;

    /**
     * @throws Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $twigMock = \Mockery::mock(Environment::class);
        $twigMock = $twigMock->shouldReceive('getLoader')->andReturn(\Mockery::self());
        $twigMock = $twigMock->shouldReceive('getPaths')->andReturn([__DIR__ . '/templates']);
        $twigMock = $twigMock->shouldReceive('render')->andReturn('OK');

        $this->testObject = new TemplateController(
            $twigMock->getMock()
        );
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

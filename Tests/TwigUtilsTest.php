<?php

namespace Prokl\StaticPageMakerBundle\Tests;

use Exception;
use Prokl\StaticPageMakerBundle\Services\Utils\TwigUtils;
use Prokl\TestingTools\Base\BaseTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

/**
 * Class TwigUtilsTest
 * @package Prokl\StaticPageMakerBundle\Tests
 */
class TwigUtilsTest extends BaseTestCase
{
    /**
     * @var TwigUtils $testObject
     */
    private $testObject;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $twigMock = \Mockery::mock(Environment::class);
        $twigMock = $twigMock->shouldReceive('getLoader')->andReturn(\Mockery::self());
        $twigMock = $twigMock->shouldReceive('getPaths')->andReturn([__DIR__ . '/templates']);

        $this->testObject = new TwigUtils(
            $twigMock->getMock(),
            new Filesystem()
        );
    }

    /**
     * Путь к шаблону существует.
     *
     * @return void
     */
    public function testGetPathTemplateExists() : void
    {
        $result = $this->testObject->getPathTemplate(
            './testing.twig'
        );

        $this->assertNotEmpty(
            $result,
            'Существующий шаблон не найден.'
        );
    }

    /**
     * Путь к шаблону не существует.
     *
     * @return void
     */
    public function testGetPathTemplateNotExists() : void
    {
        $result = $this->testObject->getPathTemplate(
            $this->faker->firstName . '.twig'
        );

        $this->assertEmpty(
            $result,
            'Несуществующий шаблон объявлен найденным.'
        );
    }

    /**
     * getModifiedTimeTemplate. Существующий шаблон.
     *
     * @return void
     */
    public function testGetModifiedTimeTemplate() : void
    {
        $result = $this->testObject->getModifiedTimeTemplate(
            './testing.twig'
        );

        $this->assertNotSame(
            0,
            $result,
            'Не определилось время изменения на существующем шаблоне.'
        );
    }

    /**
     * getModifiedTimeTemplate. Несуществующий шаблон.
     *
     * @return void
     */
    public function testGetModifiedTimeTemplateNotExist() : void
    {
        $result = $this->testObject->getModifiedTimeTemplate(
            $this->faker->firstName . '.twig'
        );

        $this->assertSame(
            0,
            $result,
            'Определилось время изменения на несуществующем шаблоне.'
        );
    }
}

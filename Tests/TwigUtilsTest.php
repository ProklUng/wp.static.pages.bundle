<?php

namespace Prokl\StaticPageMakerBundle\Tests;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Prokl\StaticPageMakerBundle\Services\Utils\TwigUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Timber\Timber;
use Twig\Environment;

/**
 * Class TwigUtilsTest
 * @package Prokl\StaticPageMakerBundle\Tests
 */
class TwigUtilsTest extends TestCase
{
    /**
     * @var TwigUtils $testObject
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
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->backupTimberLocations = Timber::$locations;

        Timber::$locations[] = $_SERVER['DOCUMENT_ROOT'] . '/utils/Bundles/StaticPageMakerBundle/Tests/templates';

        /** @var Environment $twig */
        $twig = container()->get('twig.instance');

        $this->testObject = new TwigUtils(
            $twig,
            new Filesystem()
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

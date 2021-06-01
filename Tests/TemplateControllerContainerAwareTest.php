<?php

namespace Prokl\StaticPageMakerBundle\Tests;

use Exception;
use Faker\Factory;
use Faker\Generator;
use Prokl\StaticPageMakerBundle\Services\AbstractContextProcessor;
use Prokl\StaticPageMakerBundle\Services\ContextProcessors\DefaultContextProcessorsBag;
use Prokl\StaticPageMakerBundle\Services\TemplateController;
use Prokl\StaticPageMakerBundle\Services\TemplateControllerContainerAware;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Timber\Timber;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class TemplateControllerContainerAwareTest
 * @package Prokl\StaticPageMakerBundle\Tests
 *
 * @since 25.01.2021
 */
class TemplateControllerContainerAwareTest extends TestCase
{
    /**
     * @var TemplateControllerContainerAware $testObject
     */
    private $testObject;

    /**
     * @var array $backupTimberLocations
     */
    private $backupTimberLocations = [];

    /**
     * @var ContainerBuilder $container
     */
    private $container;

    /**
     * @var Generator | null $faker
     */
    private $faker;


    /**
     * @throws Exception
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->backupTimberLocations = Timber::$locations;

        Timber::$locations[] = $_SERVER['DOCUMENT_ROOT'] . '/utils/Bundles/StaticPageMakerBundle/Tests/templates';

        /** @var Environment $twig */
        $twig = container()->get('twig.instance');

        $this->testObject = new TemplateControllerContainerAware(
            new DefaultContextProcessorsBag(),
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
     * Проверка ресолвинга переменных из контейнера.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return void
     */
    public function testResolverVariableFromContainer() : void
    {
        $value = 'test';

        $this->container = new ContainerBuilder();
        $this->container->setParameter('test.parameter', $value);

        $this->testObject->setContainer($this->container);

        $result = $this->testObject->templateAction(
            './testing.twig',
            null,
            null,
            null,
            ['testing' => '%test.parameter%',
             'assets' => [
                 'css' => '',
                 'css_page' => '',
                 'js' => '',
             ]
            ]
        );

        $this->assertStringContainsString(
            'Testing variable: ' . $value,
            (string)$result->getContent(),
            'Переменная из контейнера не обработалась.'
        );
    }

    /**
     * Проверка ресолвинга сервиса из контейнера.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return void
     */
    public function testResolverServiceFromContainer() : void
    {
        $value = 'testing service value';

        $this->container = new ContainerBuilder();
        $this->container->register('test.service', get_class($this->getTestService()));

        $this->testObject->setContainer($this->container);

        $result = $this->testObject->templateAction(
            './testing_service.twig',
            null,
            null,
            null,
            ['testing_service' => '@test.service',
                'assets' => [
                    'css' => '',
                    'css_page' => '',
                    'js' => '',
                ]
            ]
        );

        $this->assertStringContainsString(
            $value,
            (string)$result->getContent(),
            'Сервис из контейнера не обработался.'
        );
    }

    /**
     * Проверка ресолвинга алиаса сервиса в переменной из контейнера.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return void
     */
    public function testResolverAliasServiceFromContainer() : void
    {
        $value = 'testing service value';

        $this->container = new ContainerBuilder();
        $this->container->register('test.service', get_class($this->getTestService()));
        $this->container->setParameter('test.parameter', 'test.service');

        $this->testObject->setContainer($this->container);

        $result = $this->testObject->templateAction(
            './testing_alias.twig',
            null,
            null,
            null,
            ['testing' => '%test.parameter%',
                'assets' => [
                    'css' => '',
                    'css_page' => '',
                    'js' => '',
                ]
            ]
        );

        $this->assertStringContainsString(
            $value,
            (string)$result->getContent(),
            'Алиас сервиса в переменной контейнера не обработался.'
        );
    }

    /**
     * Проверка ресолвинга несуществующего сервиса из контейнера.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return void
     */
    public function testResolverNonExistingServiceFromContainer() : void
    {
        $this->initEmptyContainer();

        $result = $this->testObject->templateAction(
            './testing_service.twig',
            null,
            null,
            null,
            ['testing_service' => '@test.fake.service',
                'assets' => [
                    'css' => '',
                    'css_page' => '',
                    'js' => '',
                ]
            ]
        );

        $this->assertStringContainsString(
            'Testing service: test',
            (string)$result->getContent(),
            'Обработался фэйковый сервис из контейнера.'
        );
    }

    /**
     * Проверка ресолвинга несуществующего класса.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return void
     */
    public function testResolverNonExistingClassFromContainer() : void
    {
        $this->initEmptyContainer();

        $result = $this->testObject->templateAction(
            './testing_service.twig',
            null,
            null,
            null,
            ['testing_service' => '@FakingClasses\\FakingClass',
                'assets' => [
                    'css' => '',
                    'css_page' => '',
                    'js' => '',
                ]
            ]
        );

        $this->assertStringContainsString(
            'Testing service: test',
            (string)$result->getContent(),
            'Обработался не существующий класс.'
        );
    }

    /**
     * Проверка ресолвинга существующего класса.
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return void
     */
    public function testResolverExistingClassFromContainer() : void
    {
        $value = 'testing service value';
        $this->initEmptyContainer();

        $result = $this->testObject->templateAction(
            './testing_service.twig',
            null,
            null,
            null,
            ['testing_service' => '@' . get_class($this->getTestService()),
                'assets' => [
                    'css' => '',
                    'css_page' => '',
                    'js' => '',
                ]
            ]
        );

        $this->assertStringContainsString(
            'Testing service:' . $value . ' test',
            (string)$result->getContent(),
            'Не обработался существующий класс.'
        );
    }

    /**
     * Процессоры контента без ключа _processors.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testApplyProcessorsWithoutKeys() : void
    {
        $context = ['test' => 'testing'];

        $this->initEmptyContainer();

        $result = ReflectionObjects::callMethod(
            $this->testObject,
            'applyProcessors',
            [$context]
        );

        $this->assertSame(
            $context,
            $result,
            'Контекст изменился.'
        );
    }

    /**
     * Процессоры контента из ключа _processors.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testApplyProcessorsFromConfig() : void
    {
        $context = ['test' => 'testing', '_processors' => [
            $this->getTestContentProcessor()
        ]];

        $this->initEmptyContainer();

        $result = ReflectionObjects::callMethod(
            $this->testObject,
            'applyProcessors',
            [$context]
        );

        $this->assertSame(
            'Yes',
            $result['testing'],
            'ContextProcessor не отработал.'
        );
    }

    /**
     * Процессоры контента - не валидный класс.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testApplyInvalidProcessors() : void
    {
        $context = ['test' => 'testing', '_processors' => [
            new class {
            }
        ]];

        $this->initEmptyContainer();

        $result = ReflectionObjects::callMethod(
            $this->testObject,
            'applyProcessors',
            [$context]
        );

        $this->assertSame(
            $context,
            $result,
            'Невалидный процессор контента отработал.'
        );
    }

    /**
     * Процессоры контента из ключа _processors.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testApplyProcessorsDefault() : void
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('static_page_maker.post_type', 'seo');

        $processorsBag = new DefaultContextProcessorsBag();
        $processorsBag->setProcessors(
            new \ArrayObject([$this->getTestContentProcessor()])
        );

        /** @var Environment $twig */
        $twig = container()->get('twig.instance');

        $this->testObject = new TemplateControllerContainerAware(
            $processorsBag,
            $twig
        );
        $this->testObject->setContainer($this->container);

        $context = ['test' => 'testing'];

        $result = ReflectionObjects::callMethod(
            $this->testObject,
            'applyProcessors',
            [$context]
        );

        $this->assertSame(
            'Yes',
            $result['testing'],
            'ContextProcessor не отработал.'
        );
    }

    /**
     * Тестовый процессор контента.
     *
     * @return object
     */
    private function getTestContentProcessor() : object
    {
        return new class extends AbstractContextProcessor {
            /**
             * @inheritDoc
             */
            public function handle(): array
            {
                $this->context['testing'] = 'Yes';

                return $this->context;
            }
        };
    }

    /**
     * Тестовый сервис.
     *
     * @return object
     */
    private function getTestService()
    {
        return new class {
            public function action() : void
            {
                echo 'testing service value';
            }
        };
    }

    /**
     * Инициализировать и загнать в тестовый объект пустой контейнер.
     *
     * @return void
     */
    private function initEmptyContainer() : void
    {
        $this->container = new ContainerBuilder();
        $this->testObject->setContainer($this->container);
    }
}

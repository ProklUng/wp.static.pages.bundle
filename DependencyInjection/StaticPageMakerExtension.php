<?php

namespace Prokl\StaticPageMakerBundle\DependencyInjection;

use Exception;
use LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Twig\Environment;

/**
 * Class StaticPageMakerExtension
 * @package Prokl\StaticPageMakerBundle\DependencyInjection
 *
 * @since 23.01.2021
 */
final class StaticPageMakerExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $this->checkDependency();

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('static_page_maker.seo_iblock_id', $config['seo_iblock_id']);
        $container->setParameter('static_page_maker.set_last_modified_header', $config['set_last_modified_header']);
    }

    /**
     * @inheritDoc
     */
    public function getAlias() : string
    {
        return 'static_page_maker';
    }

    /**
     * Проверка на существование Твига.
     *
     * @return void
     * @throws LogicException Когда не найден в системе Twig.
     */
    private function checkDependency() : void
    {
        if (!class_exists(Environment::class)) {
            throw new LogicException(
                'StaticPageBundle work only with installed and configured Twig.'
            );
        }
    }
}

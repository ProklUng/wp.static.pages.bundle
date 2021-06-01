<?php

namespace Prokl\StaticPageMakerBundle\Services\Utils;

use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

/**
 * Class TwigUtils
 * @package Prokl\StaticPageMakerBundle\Services\Utils
 *
 * @since 30.01.2021
 */
class TwigUtils
{
    /**
     * @var Filesystem $filesystem
     */
    private $filesystem;

    /**
     * @var array $paths Пути к шаблонам.
     */
    private $paths;

    /**
     * TwigUtils constructor.
     *
     * @param Environment $twig       Твиг.
     * @param Filesystem  $filesystem Файловая система.
     */
    public function __construct(
        Environment $twig,
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;

        /** @psalm-suppress UndefinedInterfaceMethod */
        $this->paths = $twig->getLoader()->getPaths();
    }

    /**
     * Определить - существует ли шаблон.
     *
     * @param string $template Шаблон.
     *
     * @return string Серверный путь к шаблону.
     */
    public function getPathTemplate(string $template) : string
    {
        foreach ($this->paths as $path) {
            if ($path && $this->filesystem->exists($path . '/' .$template)) {
                return $path . '/' .$template;
            }
        }

        return '';
    }

    /**
     * Дата изменения шаблона.
     *
     * @param string $template Шаблон (без путей).
     *
     * @return integer
     */
    public function getModifiedTimeTemplate(string $template) : int
    {
        $path = $this->getPathTemplate($template);
        if (!$path) {
            return 0;
        }

        return (int)filemtime($path);
    }
}

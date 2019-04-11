<?php

declare(strict_types=1);

namespace Bolt\Twig;

use Bolt\Menu\CachedFrontendMenuBuilder;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FrontendMenuExtension extends AbstractExtension
{
    /** @var CachedFrontendMenuBuilder */
    private $menuBuilder;

    public function __construct(CachedFrontendMenuBuilder $menuBuilder)
    {
        $this->menuBuilder = $menuBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        $safe = ['is_safe' => ['html']];
        $env = ['needs_environment' => true];

        return [
            new TwigFunction('menu', [$this, 'getMenu'], $env + $safe),
            new TwigFunction('menu_json', [$this, 'getMenuJson']),
        ];
    }

    public function getMenu(Environment $twig, ?string $name = null, string $template = '_sub_menu.twig', string $class = '', bool $withsubmenus = true): string
    {
        $context = [
            'menu' => $this->menuBuilder->buildMenu($name),
            'class' => $class,
            'withsubmenus' => $withsubmenus,
        ];

        return $twig->render($template, $context);
    }

    public function getMenuJson(?string $name = null, bool $jsonPrettyPrint = false)
    {
        $menu = $this->menuBuilder->buildMenu($name);
        $options = $jsonPrettyPrint ? JSON_PRETTY_PRINT : 0;

        return json_encode($menu, $options);
    }
}

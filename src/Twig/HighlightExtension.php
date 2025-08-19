<?php

namespace Drupal\twig_highlight\Twig;

use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HighlightExtension extends AbstractExtension
{

    protected $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('highlight', [$this, 'highlight'], ['is_safe' => ['html']]),
        ];
    }

    public function highlight($source, $keyword)
    {
        if (empty($keyword) || empty(trim($keyword))) {
            return $source;
        }

        // Render arrays
        if (is_array($source)) {
            $source['#post_render'][] = function ($html, $element) use ($keyword) {
                return $this->applyHighlight($html, $keyword);
            };
            return $source;
        }

        // Strings
        if (is_string($source)) {
            return Markup::create($this->applyHighlight($source, $keyword));
        }

        // Fallback
        return $source;
    }

    protected function applyHighlight(string $html, string $keyword): string
    {
        $escaped_term = preg_quote($keyword, '/');
        return preg_replace(
            "/($escaped_term)/i",
            '<em>$1</em>',
            $html
        );
    }
}

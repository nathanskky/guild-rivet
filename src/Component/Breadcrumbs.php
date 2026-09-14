<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet breadcrumbs.
 *
 * Data-driven rather than composed from nested tags: a trail is a list of the same shape
 * repeated, and describing it as an array reads better than five nested components. The
 * last item is the current page, so it is rendered as plain text with `aria-current`
 * rather than as a link to itself.
 *
 * Each item is `['label' => string, 'href' => ?string, 'icon' => ?bool]`.
 *
 * @see https://rivet.iu.edu/components/breadcrumbs/
 */
final class Breadcrumbs extends Component
{
    /**
     * @param  list<array{label?: string, href?: string, icon?: bool}>  $items
     */
    public function __construct(
        private readonly array $items,
        private readonly string $label = 'Breadcrumbs',
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_breadcrumbs';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        if ($this->items === []) {
            throw new InvalidArgumentException(self::name() . ' needs at least one item.');
        }

        $last = array_key_last($this->items);
        $crumbs = [];

        foreach ($this->items as $position => $item) {
            $crumbs[] = $this->crumb($item, $position, $position === $last);
        }

        return Html::el('nav')
            ->attr('role', 'navigation')
            ->attr('aria-label', $this->label)
            ->merge($this->extra)
            ->children(Html::el('ol')->class('rvt-breadcrumbs')->children(...$crumbs))
            ->render();
    }

    /**
     * @param  array{label?: string, href?: string, icon?: bool}  $item
     */
    private function crumb(array $item, int $position, bool $isCurrent): Html
    {
        $label = $item['label'] ?? throw new InvalidArgumentException(
            sprintf('Breadcrumb %d needs a "label".', $position + 1),
        );

        $crumb = Html::el('li');
        $href = $item['href'] ?? null;

        if ($isCurrent || $href === null) {
            return $crumb->attr('aria-current', $isCurrent ? 'page' : null)->text($label);
        }

        $link = Html::el('a')->attr('href', $href);

        return $crumb->children(
            ($item['icon'] ?? false)
                ? $link->children(
                    Html::el('span')->class('rvt-sr-only')->text($label),
                    SvgIcon::render('home'),
                )
                : $link->text($label),
        );
    }
}

<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet dropdown menu.
 *
 * Menu items are written as bare links or buttons — Rivet styles them by position rather
 * than by class.
 *
 * No `aria-expanded` is emitted: Rivet's JavaScript adds and maintains it on the toggle,
 * and authoring one here would conflict with that.
 *
 * @see https://rivet.iu.edu/components/dropdown/
 */
final class Dropdown extends Component
{
    private const string BLOCK = 'rvt-dropdown';

    public function __construct(
        private readonly string $label,
        private readonly bool $alignRight = false,
        private readonly bool $openOnHover = false,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_dropdown';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $id = $this->resolveId($context, self::BLOCK, $this->id);

        return Html::el('div')
            ->class(self::BLOCK)
            ->attr('data-rvt-dropdown', $id)
            ->attr('data-rvt-dropdown-open-on-hover', $this->openOnHover)
            ->merge($this->extra)
            ->children(
                Html::el('button')
                    ->class('rvt-button')
                    ->attr('type', 'button')
                    ->attr('data-rvt-dropdown-toggle', true)
                    ->children(
                        Html::el('span')->class(self::BLOCK . '__toggle-text')->text($this->label),
                        SvgIcon::render('chevron-down'),
                    ),
                Html::el('div')
                    ->class(self::BLOCK . '__menu', $this->alignRight ? self::BLOCK . '__menu--right' : null)
                    ->attr('data-rvt-dropdown-menu', true)
                    ->attr('hidden', true)
                    ->html($content),
            )
            ->render();
    }
}

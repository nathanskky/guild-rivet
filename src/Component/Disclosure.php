<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet disclosure — a labelled toggle revealing a block of content.
 *
 * `aria-expanded` is authored here, unlike on a dropdown or accordion: Rivet's own
 * documentation states it as an initial value for a disclosure, and its JavaScript then
 * maintains it.
 *
 * @see https://rivet.iu.edu/components/disclosure/
 */
final class Disclosure extends Component
{
    private const string BLOCK = 'rvt-disclosure';

    public function __construct(
        private readonly string $label,
        private readonly bool $openOnInit = false,
        private readonly bool $closeOnClickOutside = false,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_disclosure';
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
            ->attr('data-rvt-disclosure', $id)
            ->attr('data-rvt-disclosure-open-on-init', $this->openOnInit)
            ->attr('data-rvt-close-click-outside', $this->closeOnClickOutside)
            ->merge($this->extra)
            ->children(
                Html::el('button')
                    ->class(self::BLOCK . '__toggle')
                    ->attr('type', 'button')
                    ->attr('data-rvt-disclosure-toggle', true)
                    ->attr('aria-expanded', $this->openOnInit ? 'true' : 'false')
                    ->text($this->label),
                Html::el('div')
                    ->class(self::BLOCK . '__content')
                    ->attr('data-rvt-disclosure-target', true)
                    ->attr('hidden', ! $this->openOnInit)
                    ->html($content),
            )
            ->render();
    }
}

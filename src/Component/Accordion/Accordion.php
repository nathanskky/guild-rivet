<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Accordion;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet accordion — a series of panels sharing one expand/collapse behaviour.
 *
 * @see https://rivet.iu.edu/components/accordion/
 */
final class Accordion extends Component
{
    private const string BLOCK = 'rvt-accordion';

    public function __construct(
        private readonly bool $openAll = false,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_accordion';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el('div')
            ->class(self::BLOCK)
            ->attr('data-rvt-accordion', $this->resolveId($context, self::BLOCK, $this->id))
            ->attr('data-rvt-accordion-open-all', $this->openAll)
            ->merge($this->extra)
            ->html($content)
            ->render();
    }
}

<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Accordion;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * One summary-and-panel pair inside an accordion.
 *
 * Emits no ARIA at all. Rivet's JavaScript generates `aria-expanded` on the trigger, the
 * trigger ids, and the `aria-labelledby` linking each panel to its summary; authoring
 * any of it here would be overwritten at best and conflict at worst. This split — which
 * attributes we supply and which Rivet's script owns — differs per component, and is
 * exactly the kind of detail this library exists to remember.
 *
 * @see https://rivet.iu.edu/components/accordion/
 */
final class AccordionPanel extends Component
{
    private const string BLOCK = 'rvt-accordion';

    public function __construct(
        private readonly string $label,
        private readonly int $headingLevel = 3,
        private readonly bool $openOnInit = false,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_accordion_panel';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $context->requireAncestor(Accordion::class, self::class);

        if ($this->headingLevel < 1 || $this->headingLevel > 6) {
            throw new InvalidArgumentException(sprintf(
                'Heading level must be between 1 and 6, %d given.',
                $this->headingLevel,
            ));
        }

        $summary = Html::el('h' . $this->headingLevel)
            ->class(self::BLOCK . '__summary')
            ->children(
                Html::el('button')
                    ->class(self::BLOCK . '__toggle')
                    ->attr('type', 'button')
                    ->attr('data-rvt-accordion-trigger', true)
                    ->children(
                        Html::el('span')->class(self::BLOCK . '__toggle-text')->text($this->label),
                        Html::el('div')->class(self::BLOCK . '__toggle-icon')->children(SvgIcon::accordionToggle()),
                    ),
            );

        $panel = Html::el('div')
            ->class(self::BLOCK . '__panel')
            ->attr('data-rvt-accordion-panel', true)
            ->attr('data-rvt-accordion-panel-init', $this->openOnInit)
            ->merge($this->extra)
            ->html($content);

        return $summary->render() . $panel->render();
    }
}

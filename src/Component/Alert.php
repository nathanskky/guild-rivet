<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Enum\AlertStyle;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet alert — a dismissible page-level message.
 *
 * Rivet's own documentation hard-codes the title id (`information-alert-title`), so two
 * alerts copied from the docs onto one page both label themselves from the first one's
 * title. Here the container, its `aria-labelledby`, and `data-rvt-alert` all derive from
 * a single id that is unique per render.
 *
 * Dismissal is driven by Rivet's JavaScript via `data-rvt-alert-close`; this component
 * ships no script of its own.
 *
 * @see https://rivet.iu.edu/components/alert/
 */
final class Alert extends Component
{
    private const string BLOCK = 'rvt-alert';

    public function __construct(
        private readonly string $title,
        private readonly AlertStyle $style = AlertStyle::Info,
        private readonly bool $dismissible = true,
        private readonly ?string $id = null,
        private readonly string $dismissLabel = 'Dismiss this alert',
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_alert';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $id = $this->resolveId($context, self::BLOCK, $this->id);
        $titleId = $id . '-title';

        return Html::el('div')
            ->class(self::BLOCK, self::BLOCK . '--' . $this->style->value)
            ->attr('role', 'alert')
            ->attr('aria-labelledby', $titleId)
            ->attr('data-rvt-alert', $id)
            ->merge($this->extra)
            ->children(
                Html::el('div')->class(self::BLOCK . '__title')->attr('id', $titleId)->text($this->title),
                Html::el('p')->class(self::BLOCK . '__message')->html($content),
                $this->dismissible ? $this->dismissButton() : null,
            )
            ->render();
    }

    private function dismissButton(): Html
    {
        return Html::el('button')
            ->class(self::BLOCK . '__dismiss')
            ->attr('type', 'button')
            ->attr('data-rvt-alert-close', true)
            ->children(
                Html::el('span')->class('rvt-sr-only')->text($this->dismissLabel),
                SvgIcon::render('close'),
            );
    }
}

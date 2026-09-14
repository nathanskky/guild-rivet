<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Enum\BadgeStyle;
use Guild\Rivet\Enum\BadgeVariant;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Html\Modifier;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet badge — a short status annotation.
 *
 * @see https://rivet.iu.edu/components/badge/
 */
final class Badge extends Component
{
    private const string BLOCK = 'rvt-badge';

    public function __construct(
        private readonly string $text,
        private readonly BadgeStyle $style = BadgeStyle::Base,
        private readonly BadgeVariant $variant = BadgeVariant::Solid,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_badge';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el('span')
            ->class(self::BLOCK, $this->modifier())
            ->merge($this->extra)
            ->text($this->text)
            ->render();
    }

    private function modifier(): ?string
    {
        return Modifier::compose(
            self::BLOCK,
            $this->style === BadgeStyle::Base ? null : $this->style->value,
            $this->variant === BadgeVariant::Secondary,
        );
    }
}

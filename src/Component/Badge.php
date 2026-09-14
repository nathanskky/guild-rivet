<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Enum\BadgeStyle;
use Guild\Rivet\Enum\BadgeVariant;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
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

    /**
     * Compose the two axes into the single modifier Rivet defines.
     *
     * Base + solid is the unmodified block; every other combination appends a suffix,
     * and secondary hyphenates onto a non-base style (`rvt-badge--danger-secondary`).
     */
    private function modifier(): ?string
    {
        $isSecondary = $this->variant === BadgeVariant::Secondary;

        if ($this->style === BadgeStyle::Base) {
            return $isSecondary ? self::BLOCK . '--secondary' : null;
        }

        return self::BLOCK . '--' . $this->style->value . ($isSecondary ? '-secondary' : '');
    }
}

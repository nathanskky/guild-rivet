<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Enum\ListStyle;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet list.
 *
 * Named ListComponent because `List` is a reserved word in PHP; the template-facing name
 * is still `rvt_list`.
 *
 * @see https://rivet.iu.edu/components/list/
 */
final class ListComponent extends Component
{
    public function __construct(
        private readonly ListStyle $style = ListStyle::Default,
        private readonly bool $ordered = false,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_list';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el($this->element())
            ->class($this->style->value)
            ->merge($this->extra)
            ->html($content)
            ->render();
    }

    /**
     * A description list has no ordered form, so the flag cannot change its element.
     */
    private function element(): string
    {
        return match (true) {
            $this->style === ListStyle::Description => 'dl',
            $this->ordered => 'ol',
            default => 'ul',
        };
    }
}

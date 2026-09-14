<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Tabs;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Render\RenderContext;

/**
 * One tab and its panel, written together.
 *
 * Renders nothing itself. It hands its label and body to the enclosing Tabs, which emits
 * the buttons and the panels as the two separate runs Rivet requires.
 *
 * @see https://rivet.iu.edu/components/tabs/
 */
final class Tab extends Component
{
    public function __construct(
        private readonly string $label,
        private readonly bool $openOnInit = false,
    ) {
    }

    public static function name(): string
    {
        return 'rvt_tab';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $context->requireAncestor(Tabs::class, self::class)
            ->addTab($this->label, $content, $this->openOnInit);

        return '';
    }
}

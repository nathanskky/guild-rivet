<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * A Rivet icon, as the `<rvt-icon>` element Rivet documents as the preferred form.
 *
 * Requires the separate `rivet-icons` package:
 *
 * ```html
 * <link rel="stylesheet" href="https://unpkg.com/rivet-icons@3.0.1/dist/rivet-icon-element.css">
 * <script type="module" src="https://unpkg.com/rivet-icons@3.0.1/dist/rivet-icons.js"></script>
 * ```
 *
 * Icons this library embeds in its own components use inline SVG instead, so those
 * components keep working without that package.
 *
 * @see https://rivet.iu.edu/icons-stickers/icons/
 */
final class Icon extends Component
{
    public function __construct(
        private readonly string $name,
        private readonly ?string $label = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_icon';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $decorative = $this->label === null || $this->label === '';

        return Html::el('rvt-icon')
            ->attr('name', $this->name)
            ->attr('role', $decorative ? null : 'img')
            ->attr('aria-label', $decorative ? null : $this->label)
            ->attr('aria-hidden', $decorative ? 'true' : null)
            ->merge($this->extra)
            ->render();
    }
}

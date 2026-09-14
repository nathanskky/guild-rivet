<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Enum\TableStyle;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet table.
 *
 * A caption is always emitted, visually hidden unless asked for: Rivet requires one, and
 * it is what names the scroll region of a responsive table. Rivet's documentation calls
 * out explicitly that several responsive tables on a page need unique id and
 * aria-labelledby values, which is handled here by generating them together.
 *
 * @see https://rivet.iu.edu/components/table/
 */
final class Table extends Component
{
    private const string BLOCK = 'rvt-table';

    public function __construct(
        private readonly string $caption,
        private readonly TableStyle $style = TableStyle::Default,
        private readonly bool $captionVisible = false,
        private readonly bool $responsive = false,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_table';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $captionId = $this->resolveId($context, self::BLOCK, $this->id) . '-caption';

        $table = Html::el('table')
            ->class($this->style->value)
            ->merge($this->extra)
            ->children(
                Html::el('caption')
                    ->class($this->captionVisible ? null : 'rvt-sr-only')
                    ->attr('id', $captionId)
                    ->text($this->caption),
            )
            ->html($content);

        if (! $this->responsive) {
            return $table->render();
        }

        return Html::el('div')
            ->class(self::BLOCK . '-responsive')
            ->attr('role', 'region')
            ->attr('tabindex', '0')
            ->attr('aria-labelledby', $captionId)
            ->children($table)
            ->render();
    }
}

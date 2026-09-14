<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Enum\AlertStyle;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet inline alert — the short message that sits under a form field.
 *
 * Each severity carries its own glyph, because colour alone must not be the only thing
 * distinguishing them. The message always has an id so a field can point its
 * `aria-describedby` at it; pass `id` explicitly when wiring one up by hand.
 *
 * Unlike Alert this needs no JavaScript and cannot be dismissed.
 *
 * @see https://rivet.iu.edu/components/alert/
 */
final class InlineAlert extends Component
{
    private const string BLOCK = 'rvt-inline-alert';

    public function __construct(
        private readonly ?string $message = null,
        private readonly AlertStyle $style = AlertStyle::Info,
        private readonly bool $standalone = false,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_inline_alert';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $messageId = $this->resolveId($context, self::BLOCK, $this->id);

        $message = Html::el('span')->class(self::BLOCK . '__message')->attr('id', $messageId);
        $message = $content === ''
            ? $message->text($this->message ?? '')
            : $message->html($content);

        return Html::el('div')
            ->class(
                self::BLOCK,
                $this->standalone ? self::BLOCK . '--standalone' : null,
                self::BLOCK . '--' . $this->style->value,
            )
            ->merge($this->extra)
            ->children(
                Html::el('span')
                    ->class(self::BLOCK . '__icon')
                    ->children(SvgIcon::render('alert-' . $this->style->value)),
                $message,
            )
            ->render();
    }
}

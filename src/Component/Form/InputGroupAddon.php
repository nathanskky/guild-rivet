<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Enum\AddonPosition;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Something attached to one end of an input group: a unit, a prefix, or a button.
 *
 * Pass `text` for a plain label, or write content between the tags for anything else —
 * the search pattern in Rivet appends a button rather than text.
 *
 * @see https://rivet.iu.edu/components/input-group/
 */
final class InputGroupAddon extends Component
{
    private const string BLOCK = 'rvt-input-group';

    public function __construct(
        private readonly ?string $text = null,
        private readonly AddonPosition $position = AddonPosition::Append,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_input_group_addon';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $context->requireAncestor(InputGroup::class, self::class);

        $wrapper = Html::el('div')
            ->class(self::BLOCK . '__' . $this->position->value)
            ->merge($this->extra);

        if ($content !== '') {
            return $wrapper->html($content)->render();
        }

        return $wrapper
            ->children(
                Html::el('div')
                    ->class(self::BLOCK . '__text')
                    ->attr('id', $this->id)
                    ->text($this->text ?? ''),
            )
            ->render();
    }
}

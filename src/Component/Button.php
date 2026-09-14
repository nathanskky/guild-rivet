<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Enum\ButtonFill;
use Guild\Rivet\Enum\ButtonPurpose;
use Guild\Rivet\Enum\ButtonSize;
use Guild\Rivet\Enum\ButtonType;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Html\Modifier;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet button.
 *
 * Pass `text` for the common case, or write content between the tags to combine an icon
 * with a label. Purpose, fill, size and full width are independent axes composed into
 * Rivet's classes by one function, rather than a single enum mirroring every class name —
 * Rivet 3 removes the success, danger and plain modifiers outright, and this keeps that
 * change confined to the mapping.
 *
 * @see https://rivet.iu.edu/components/button/
 */
final class Button extends Component
{
    private const string BLOCK = 'rvt-button';

    public function __construct(
        private readonly ?string $text = null,
        private readonly ButtonPurpose $purpose = ButtonPurpose::Default,
        private readonly ButtonFill $fill = ButtonFill::Solid,
        private readonly ButtonSize $size = ButtonSize::Default,
        private readonly bool $fullWidth = false,
        private readonly ButtonType $type = ButtonType::Button,
        private readonly bool $disabled = false,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_button';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $button = Html::el('button')
            ->class(
                self::BLOCK,
                $this->purposeModifier(),
                $this->size === ButtonSize::Small ? self::BLOCK . '--small' : null,
                $this->fullWidth ? self::BLOCK . '--full-width' : null,
            )
            ->attr('type', $this->type->value)
            ->attr('disabled', $this->disabled)
            ->merge($this->extra);

        if ($content !== '') {
            return $button->html($content)->render();
        }

        if ($this->text === null || $this->text === '') {
            throw new InvalidArgumentException(
                self::name() . ' needs either text or content to give it an accessible name.',
            );
        }

        return $button->text($this->text)->render();
    }

    private function purposeModifier(): ?string
    {
        $secondary = $this->fill === ButtonFill::Outline;

        if ($this->purpose === ButtonPurpose::Plain && $secondary) {
            throw new InvalidArgumentException('Rivet has no outline variant for a plain button.');
        }

        return Modifier::compose(
            self::BLOCK,
            $this->purpose === ButtonPurpose::Default ? null : $this->purpose->value,
            $secondary,
        );
    }
}

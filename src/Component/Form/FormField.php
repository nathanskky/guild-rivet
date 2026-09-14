<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Component\InlineAlert;
use Guild\Rivet\Enum\AlertStyle;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Label, control, helper text and validation messages for one form field.
 *
 * This is where the library pays for itself on forms. Rivet requires an explicit
 * `for`/`id` pairing on every control, and a control that has helper text or an error
 * must point `aria-describedby` at exactly the sections that exist — a reference to an
 * id that was never rendered reads as nothing at all. Both are easy to get wrong by
 * hand, and forms are usually rendered in loops where a hard-coded id collides.
 *
 * The field owns one id, derives every other from it, and tells the control inside it
 * what to use. Controls read that by finding this component on the render stack, which
 * is why they must be written inside it.
 *
 * No wrapper element is emitted: Rivet documents label, control and message as siblings,
 * so inventing a container would depart from the markup this library exists to reproduce.
 *
 * @see https://rivet.iu.edu/components/text-input/
 */
final class FormField extends Component
{
    private const string ID_PREFIX = 'rvt-field';

    public function __construct(
        private readonly string $label,
        private readonly bool $required = false,
        private readonly bool $labelHidden = false,
        private readonly ?string $helperText = null,
        /** @var list<string> */
        private readonly array $errors = [],
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_form_field';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    /**
     * The id the control inside this field must carry.
     *
     * Resolved on first request, which is normally the control asking during rendering
     * of the field's body — before this component renders itself.
     */
    public function fieldId(RenderContext $context): string
    {
        return $this->resolveId($context, self::ID_PREFIX, $this->id);
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * The value for the control's `aria-describedby`, or null when nothing describes it.
     *
     * Only sections that actually rendered are listed. Valid once the field has resolved
     * its id.
     */
    public function describedBy(): ?string
    {
        $id = $this->resolvedIdOrNull();

        if ($id === null) {
            return null;
        }

        $ids = [];

        if ($this->helperText !== null && $this->helperText !== '') {
            $ids[] = $id . '-helper';
        }

        foreach (array_keys($this->errors) as $position) {
            $ids[] = $id . '-error-' . ($position + 1);
        }

        return $ids === [] ? null : implode(' ', $ids);
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $id = $this->fieldId($context);

        return $this->labelElement($id)->render()
            . $content
            . $this->helperElement($id)
            . $this->errorElements($context, $id);
    }

    private function labelElement(string $id): Html
    {
        $label = Html::el('label')
            ->class('rvt-label', $this->labelHidden ? 'rvt-sr-only' : null)
            ->attr('for', $id)
            ->merge($this->extra)
            ->text($this->label);

        // Rivet spells the required marker as literal markup inside the label; a form
        // using it should also explain the asterisk somewhere above the fields.
        return $this->required
            ? $label->html(' ')->children(
                Html::el('span')->class('rvt-color-orange-500', 'rvt-text-bold')->text('*'),
            )
            : $label;
    }

    private function helperElement(string $id): string
    {
        if ($this->helperText === null || $this->helperText === '') {
            return '';
        }

        return Html::el('div')
            ->class('rvt-ts-14', 'rvt-color-black-500', 'rvt-m-top-xxs')
            ->attr('id', $id . '-helper')
            ->text($this->helperText)
            ->render();
    }

    private function errorElements(RenderContext $context, string $id): string
    {
        $rendered = '';

        foreach ($this->errors as $position => $error) {
            $rendered .= new InlineAlert(
                message: $error,
                style: AlertStyle::Danger,
                id: $id . '-error-' . ($position + 1),
            )->render($context);
        }

        return $rendered;
    }
}

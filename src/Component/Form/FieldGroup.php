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
 * A fieldset grouping several related choices under one legend.
 *
 * The counterpart to FormField for controls that carry their own labels. A text input
 * takes one label from the field above it; a set of checkboxes or radios each carry
 * their own, and what they share is the question being asked — which is what the legend
 * states. Rivet requires this structure for radios and recommends it for checkboxes.
 *
 * Helper text and validation messages belong to the group, and every choice inside
 * references them, since a message such as "pick one" describes the whole set.
 *
 * @see https://rivet.iu.edu/components/radio-input/
 */
final class FieldGroup extends Component
{
    private const string ID_PREFIX = 'rvt-field-group';

    public function __construct(
        private readonly string $legend,
        private readonly bool $legendHidden = false,
        private readonly bool $inline = false,
        private readonly ?string $helperText = null,
        /** @var list<string> */
        private readonly array $errors = [],
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_field_group';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function groupId(RenderContext $context): string
    {
        return $this->resolveId($context, self::ID_PREFIX, $this->id);
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * The describedby value every choice in this group should carry, or null.
     *
     * Takes the context so it settles the group's id itself: choices ask for this while
     * the group's body renders, before the group has rendered anything of its own.
     */
    public function describedBy(RenderContext $context): ?string
    {
        $id = $this->groupId($context);
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
        $id = $this->groupId($context);

        return Html::el('fieldset')
            ->class('rvt-fieldset')
            ->merge($this->extra)
            ->children(
                Html::el('legend')->class($this->legendHidden ? 'rvt-sr-only' : null)->text($this->legend),
                Html::el('ul')->class($this->inline ? 'rvt-list-inline' : 'rvt-list-plain')->html($content),
            )
            ->html($this->helperElement($id) . $this->errorElements($context, $id))
            ->render();
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

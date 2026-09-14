<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet switch — an immediate on/off control.
 *
 * Named ToggleSwitch because `Switch` is a reserved word in PHP; the template-facing
 * name is still `rvt_switch`.
 *
 * Rivet builds this from a button rather than a checkbox, so the role and the label are
 * both required from us: the visible "On" and "Off" spans are decoration, and without a
 * label a screen reader announces an unnamed switch. `aria-checked` is deliberately not
 * emitted — Rivet's JavaScript owns it, and setting it here would fight that.
 *
 * Rivet's guidance is to use a checkbox, not a switch, for a yes/no question on a form
 * that is submitted; a switch takes effect immediately.
 *
 * @see https://rivet.iu.edu/components/switch/
 */
final class ToggleSwitch extends Component
{
    private const string BLOCK = 'rvt-switch';

    public function __construct(
        private readonly string $label,
        private readonly bool $on = false,
        private readonly bool $small = false,
        private readonly bool $danger = false,
        private readonly string $onText = 'On',
        private readonly string $offText = 'Off',
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_switch';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        if ($this->label === '') {
            throw new InvalidArgumentException(self::name() . ' requires a label.');
        }

        $id = $this->resolveId($context, self::BLOCK, $this->id);

        return Html::el('button')
            ->class(
                self::BLOCK,
                $this->small ? self::BLOCK . '--small' : null,
                $this->danger ? self::BLOCK . '--danger' : null,
            )
            ->attr('type', 'button')
            ->attr('data-rvt-switch', $id)
            ->attr('data-rvt-switch-on', $this->on)
            ->attr('role', 'switch')
            ->attr('aria-label', $this->label)
            ->merge($this->extra)
            ->children(
                Html::el('span')->class(self::BLOCK . '__on')->text($this->onText),
                Html::el('span')->class(self::BLOCK . '__off')->text($this->offText),
            )
            ->render();
    }
}

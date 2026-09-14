<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Enum\StepStatus;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet step indicator — progress through a multi-step task.
 *
 * Each step is `['label' => string, 'href' => ?string, 'current' => ?bool,
 * 'status' => ?StepStatus]`.
 *
 * @see https://rivet.iu.edu/components/step-indicator/
 */
final class StepIndicator extends Component
{
    private const string BLOCK = 'rvt-steps';

    /**
     * @param  list<array{label?: string, href?: string, current?: bool, status?: StepStatus}>  $steps
     */
    public function __construct(
        private readonly array $steps = [],
        private readonly bool $vertical = false,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_step_indicator';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $list = Html::el('ol')
            ->class(self::BLOCK, $this->vertical ? self::BLOCK . '--vertical' : null)
            ->merge($this->extra);

        foreach ($this->steps as $position => $step) {
            $list->children($this->step($step, $position));
        }

        return $list->render();
    }

    /**
     * @param  array{label?: string, href?: string, current?: bool, status?: StepStatus}  $step
     */
    private function step(array $step, int $position): Html
    {
        $label = $step['label'] ?? throw new InvalidArgumentException(
            sprintf('Step %d needs a "label".', $position + 1),
        );

        $status = $step['status'] ?? StepStatus::Default;

        $indicator = Html::el('span')->class(
            self::BLOCK . '__indicator',
            $status === StepStatus::Default ? null : self::BLOCK . '__indicator--' . $status->value,
        );

        // Rivet names the sequence once, on the first indicator, rather than repeating
        // "Step" before every number.
        if ($position === 0) {
            $indicator->children(Html::el('span')->class('rvt-sr-only')->text('Step'))->text(' ' . ($position + 1));
        } else {
            $indicator->text((string) ($position + 1));
        }

        return Html::el('li')
            ->class(self::BLOCK . '__item')
            ->children(
                Html::el('a')
                    ->class(self::BLOCK . '__item-content')
                    ->attr('href', $step['href'] ?? null)
                    ->attr('aria-current', ($step['current'] ?? false) ? 'step' : null)
                    ->children(
                        Html::el('span')->class(self::BLOCK . '__label')->text($label),
                        $indicator,
                    ),
            );
    }
}

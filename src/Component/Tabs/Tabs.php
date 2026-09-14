<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Tabs;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet tabs.
 *
 * Rivet pairs a tab to its panel by position, and needs every button emitted before
 * every panel. Authors think in pairs, so each Tab registers its label and body here
 * while the body is being captured, and this component lays the two runs out afterwards.
 * The captured content is therefore not used directly — the tabs themselves render to
 * nothing.
 *
 * Only `aria-label` is authored. Rivet's JavaScript supplies every role, `aria-selected`
 * and `aria-controls` at init, and would conflict with any we emitted.
 *
 * @see https://rivet.iu.edu/components/tabs/
 */
final class Tabs extends Component
{
    private const string BLOCK = 'rvt-tabs';

    /** @var list<array{label: string, content: string, openOnInit: bool}> */
    private array $tabs = [];

    public function __construct(
        private readonly string $label,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_tabs';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    /**
     * Called by each Tab as the body is captured.
     */
    public function addTab(string $label, string $content, bool $openOnInit): void
    {
        $this->tabs[] = ['label' => $label, 'content' => $content, 'openOnInit' => $openOnInit];
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        if ($this->label === '') {
            throw new InvalidArgumentException(self::name() . ' requires a label naming the tab list.');
        }

        $buttons = [];
        $panels = [];

        foreach ($this->tabs as $tab) {
            $buttons[] = Html::el('button')
                ->class(self::BLOCK . '__tab')
                ->attr('type', 'button')
                ->attr('data-rvt-tab', true)
                ->text($tab['label']);

            $panels[] = Html::el('div')
                ->class(self::BLOCK . '__panel')
                ->attr('data-rvt-tab-panel', true)
                ->attr('data-rvt-tab-init', $tab['openOnInit'])
                ->html($tab['content']);
        }

        return Html::el('div')
            ->class(self::BLOCK)
            ->attr('data-rvt-tabs', $this->resolveId($context, self::BLOCK, $this->id))
            ->merge($this->extra)
            ->children(
                Html::el('div')
                    ->class(self::BLOCK . '__tablist')
                    ->attr('aria-label', $this->label)
                    ->attr('data-rvt-tablist', true)
                    ->children(...$buttons),
                ...$panels,
            )
            ->render();
    }
}

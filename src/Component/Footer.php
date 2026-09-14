<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Enum\ContainerSize;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet base footer.
 *
 * IU requires an accessibility link, a privacy notice, a copyright line carrying the
 * current year, and the trident unmodified. Those are built in rather than left to the
 * caller to remember, and the hrefs default to IU's own pages. Extra links sit above the
 * copyright, which always closes the list.
 *
 * @see https://rivet.iu.edu/components/footer/
 */
final class Footer extends Component
{
    private const string BLOCK = 'rvt-footer-base';

    /**
     * @param  list<array{label?: string, href?: string}>  $links
     */
    public function __construct(
        private readonly array $links = [],
        private readonly bool $light = false,
        private readonly ContainerSize $containerSize = ContainerSize::Large,
        private readonly string $accessibilityHref = 'https://accessibility.iu.edu/assistance/',
        private readonly string $privacyHref = 'https://privacy.iu.edu/privacy/',
        private readonly string $copyrightHref = 'https://www.iu.edu/copyright/index.html',
        private readonly string $holder = 'Indiana University',
        private readonly string $holderHref = 'https://www.iu.edu',
        private readonly ?int $year = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_footer';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $list = Html::el('ul')->class(self::BLOCK . '__list');

        foreach ($this->links as $link) {
            $list->children($this->item(
                Html::el('a')
                    ->class(self::BLOCK . '__link')
                    ->attr('href', $link['href'] ?? null)
                    ->text($link['label'] ?? ''),
            ));
        }

        $list
            ->children($this->item($this->link($this->accessibilityHref, 'Accessibility')))
            ->children($this->item($this->link($this->privacyHref, 'Privacy Notice')))
            ->children($this->copyright());

        return Html::el('footer')
            ->class(self::BLOCK, $this->light ? self::BLOCK . '--light' : null)
            ->merge($this->extra)
            ->children(
                Html::el('div')->class($this->containerSize->value)->children(
                    Html::el('div')->class(self::BLOCK . '__inner')->children(
                        Html::el('div')->class(self::BLOCK . '__logo')->children(SvgIcon::footerTrident()),
                        $list,
                    ),
                ),
            )
            ->render();
    }

    private function item(Html $child): Html
    {
        return Html::el('li')->class(self::BLOCK . '__item')->children($child);
    }

    private function link(string $href, string $label): Html
    {
        return Html::el('a')->class(self::BLOCK . '__link')->attr('href', $href)->text($label);
    }

    /**
     * The year defaults to now, because a stale copyright line is the usual failure here.
     */
    private function copyright(): Html
    {
        return Html::el('li')
            ->class(self::BLOCK . '__item')
            ->children($this->link($this->copyrightHref, 'Copyright'))
            ->text(' © ' . ($this->year ?? (int) date('Y')) . ' The Trustees of ')
            ->children($this->link($this->holderHref, $this->holder));
    }
}

<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Page;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Component\Footer;
use Guild\Rivet\Component\Header;
use Guild\Rivet\Enum\PageLayout;
use Guild\Rivet\Exception\ConfigurationException;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Page\PageDefaults;
use Guild\Rivet\Render\RenderContext;
use Guild\Rivet\Render\StartsRender;

/**
 * A complete Rivet blank-page document.
 *
 * Assembles doctype, head, header, layout, footer and scripts, so an application's page
 * template carries only what is specific to that page. Application-wide values come from
 * PageDefaults; per-page regions come from the slot components, which register with this
 * one as the body is captured.
 *
 * Implements StartsRender, so opening a page restarts id numbering and the same page
 * always renders identically.
 *
 * @see https://rivet.iu.edu/layouts/blank-page/
 */
final class Page extends Component implements StartsRender
{
    private string $styles = '';

    private string $scripts = '';

    private string $sidebar = '';

    private string $breadcrumbs = '';

    public function __construct(
        private readonly ?string $title = null,
        private readonly ?string $heading = null,
        private readonly ?string $description = null,
        private readonly PageLayout $layout = PageLayout::SingleColumn,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_page';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function layout(): PageLayout
    {
        return $this->layout;
    }

    public function addStyles(string $html): void
    {
        $this->styles .= $html;
    }

    public function addScripts(string $html): void
    {
        $this->scripts .= $html;
    }

    public function setSidebar(string $html): void
    {
        $this->sidebar = $html;
    }

    public function setBreadcrumbs(string $html): void
    {
        $this->breadcrumbs = $html;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $defaults = $context->pageDefaults() ?? throw new ConfigurationException(
            'rvt_page needs PageDefaults. Pass one to the Renderer constructor, '
            . 'or to addRivet() in a Guild application.'
        );

        return '<!doctype html>'
            . Html::el('html')
                ->attr('lang', $defaults->lang)
                ->merge($this->extra)
                ->html($this->head($defaults) . $this->body($context, $defaults, $content))
                ->render();
    }

    private function head(PageDefaults $defaults): string
    {
        $head = Html::el('head')
            ->html('<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">')
            ->children(Html::el('title')->text($defaults->documentTitle($this->title)));

        $description = $this->description ?? $defaults->description;

        if ($description !== null && $description !== '') {
            $head->children(Html::el('meta')->attr('name', 'description')->attr('content', $description));
        }

        if ($defaults->assets->enabled) {
            $head->children(Html::el('link')->attr('rel', 'stylesheet')->attr('href', $defaults->assets->coreCss()));
        }

        if ($defaults->assets->icons) {
            $head->children(Html::el('link')->attr('rel', 'stylesheet')->attr('href', $defaults->assets->iconsCss()));
        }

        return $head->html($this->styles)->render();
    }

    private function body(RenderContext $context, PageDefaults $defaults, string $content): string
    {
        return Html::el('body')
            ->class('rvt-layout')
            ->html(
                $this->header($context, $defaults)
                . $this->main($defaults, $content)
                . $this->footer($context, $defaults)
                . $this->trailingScripts($defaults)
            )
            ->render();
    }

    private function header(RenderContext $context, PageDefaults $defaults): string
    {
        return new Header(
            title: $defaults->appTitle,
            subtitle: $defaults->appSubtitle,
            href: $defaults->homeHref,
            items: $defaults->navItems,
            containerSize: $defaults->containerSize,
            searchAction: $defaults->searchAction,
        )->render($context);
    }

    private function footer(RenderContext $context, PageDefaults $defaults): string
    {
        return new Footer(
            links: $defaults->footerLinks,
            light: $defaults->footerLight,
            containerSize: $defaults->containerSize,
        )->render($context);
    }

    /**
     * Rivet's three blank-page layouts are structurally different documents rather than
     * one structure with modifiers.
     *
     * The single-column layout has no sidebar region, so a sidebar reaching it means an
     * rvt_page_sidebar slot was used without asking for one of the two sidebar layouts,
     * which the codebase's fail-loudly convention says should surface immediately rather
     * than being silently dropped.
     *
     * Anchoring the sidebar to the viewport edge means main becomes the layout wrapper
     * itself, which leaves nowhere above it for the full-bleed heading band the other two
     * layouts use; breadcrumbs and the heading move into the content region instead.
     */
    private function main(PageDefaults $defaults, string $content): string
    {
        if ($this->sidebar !== '' && $this->layout === PageLayout::SingleColumn) {
            throw new InvalidArgumentException(sprintf(
                '%s has nowhere to go in the %s layout. Use the %s or %s layout.',
                PageSidebar::name(),
                PageLayout::SingleColumn->value,
                PageLayout::Sidebar->value,
                PageLayout::AnchoredSidebar->value,
            ));
        }

        // Anchoring the sidebar to the viewport edge means main becomes the layout
        // wrapper itself, which leaves nowhere above it for a full-bleed heading band.
        if ($this->layout === PageLayout::AnchoredSidebar) {
            return Html::el('main')
                ->attr('id', 'main-content')
                ->class('rvt-layout__wrapper', 'rvt-layout__wrapper--details')
                ->html($this->anchoredSidebar() . $this->anchoredContent($defaults, $content))
                ->render();
        }

        return Html::el('main')
            ->attr('id', 'main-content')
            ->class('rvt-flex', 'rvt-flex-column', 'rvt-grow-1')
            ->html($this->headingBand($defaults) . $this->wrapper($defaults, $content))
            ->render();
    }

    private function wrapper(PageDefaults $defaults, string $content): string
    {
        if ($this->layout === PageLayout::Sidebar) {
            return Html::el('div')
                ->class('rvt-layout__wrapper', 'rvt-layout__wrapper--details', $defaults->containerSize->value)
                ->children(
                    Html::el('div')
                        ->class('rvt-layout__sidebar', 'rvt-p-top-xxl', 'rvt-flow', 'rvt-prose')
                        ->attr('id', 'section-nav')
                        ->html($this->sidebar),
                    Html::el('div')->class('rvt-layout__content', 'rvt-p-top-xxl')->html($content),
                )
                ->render();
        }

        return Html::el('div')
            ->class('rvt-layout__wrapper', 'rvt-p-tb-xxl')
            ->children(Html::el('div')->class($defaults->containerSize->value)->html($content))
            ->render();
    }

    private function anchoredSidebar(): string
    {
        return Html::el('div')
            ->class('rvt-layout__sidebar', 'rvt-p-top-xxl', 'rvt-p-left-md', 'rvt-bg-black-000')
            ->attr('id', 'section-nav')
            ->html($this->sidebar)
            ->render();
    }

    private function anchoredContent(PageDefaults $defaults, string $content): string
    {
        $inner = Html::el('div')
            ->class(
                $defaults->containerSize->value,
                'rvt-m-top-xl',
                'rvt-m-left-none',
                'rvt-m-right-none',
                'rvt-p-right-none',
                'rvt-p-left-none',
            );

        $heading = $this->headingBlock();

        if ($heading !== '') {
            $inner->html($heading);
        }

        return Html::el('div')
            ->class('rvt-layout__content', 'rvt-p-top-xxl', 'rvt-p-lr-md', 'rvt-p-lr-xxl-md-up')
            ->children($inner->html($content))
            ->render();
    }

    /**
     * Breadcrumbs and the heading, without the band around them.
     */
    private function headingBlock(): string
    {
        if ($this->heading === null && $this->breadcrumbs === '') {
            return '';
        }

        $block = Html::el('div')->class('rvt-prose')->html($this->breadcrumbs);

        if ($this->heading !== null) {
            $block->children(Html::el('h1')->class('rvt-m-top-xs')->text($this->heading));
        }

        return $block->render();
    }

    /**
     * The shaded, full-bleed band holding breadcrumbs and the page heading.
     *
     * Omitted entirely when there is neither. Not used by the anchored-sidebar layout,
     * which has nowhere above main to put a full-bleed band.
     */
    private function headingBand(PageDefaults $defaults): string
    {
        if ($this->heading === null && $this->breadcrumbs === '') {
            return '';
        }

        $inner = Html::el('div')
            ->class($defaults->containerSize->value, 'rvt-prose', 'rvt-flow', 'rvt-p-bottom-xl')
            ->html($this->breadcrumbs);

        if ($this->heading !== null) {
            $inner->children(Html::el('h1')->class('rvt-m-top-xs')->text($this->heading));
        }

        return Html::el('div')
            ->class('rvt-bg-black-000', 'rvt-border-bottom', 'rvt-p-top-xl')
            ->children($inner)
            ->render();
    }

    private function trailingScripts(PageDefaults $defaults): string
    {
        $scripts = '';

        if ($defaults->assets->enabled) {
            $scripts .= Html::el('script')->attr('src', $defaults->assets->coreJs())->render()
                . '<script>Rivet.init()</script>';
        }

        if ($defaults->assets->icons) {
            $scripts .= Html::el('script')
                ->attr('type', 'module')
                ->attr('src', $defaults->assets->iconsJs())
                ->render();
        }

        return $scripts . $this->scripts;
    }
}

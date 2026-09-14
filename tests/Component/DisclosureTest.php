<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Accordion\Accordion;
use Guild\Rivet\Component\Accordion\AccordionPanel;
use Guild\Rivet\Component\Disclosure;
use Guild\Rivet\Component\Dropdown;
use Guild\Rivet\Component\Tabs\Tab;
use Guild\Rivet\Component\Tabs\Tabs;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Disclosure::class)]
#[CoversClass(Dropdown::class)]
#[CoversClass(Accordion::class)]
#[CoversClass(AccordionPanel::class)]
#[CoversClass(Tabs::class)]
#[CoversClass(Tab::class)]
final class DisclosureTest extends TestCase
{
    public function testADisclosureStatesItsInitialExpandedState(): void
    {
        self::assertSame(
            '<div class="rvt-disclosure" data-rvt-disclosure="rvt-disclosure-1">'
            . '<button class="rvt-disclosure__toggle" type="button" data-rvt-disclosure-toggle aria-expanded="false">The numbers</button>'
            . '<div class="rvt-disclosure__content" data-rvt-disclosure-target hidden>content</div>'
            . '</div>',
            new Disclosure(label: 'The numbers')->render(new RenderContext(), 'content'),
            'Rivet authors aria-expanded as an initial state here, then its JavaScript maintains it.',
        );
    }

    public function testADisclosureCanStartOpenAndCloseOnOutsideClicks(): void
    {
        $html = new Disclosure(label: 'x', openOnInit: true, closeOnClickOutside: true)
            ->render(new RenderContext(), 'c');

        self::assertStringContainsString('data-rvt-disclosure-open-on-init', $html);
        self::assertStringContainsString('data-rvt-close-click-outside', $html);
    }

    public function testADropdownLeavesExpandedStateEntirelyToRivet(): void
    {
        $html = new Dropdown(label: 'Actions')->render(new RenderContext(), '<a href="#">One</a>');

        self::assertStringContainsString('<span class="rvt-dropdown__toggle-text">Actions</span>', $html);
        self::assertStringContainsString('<div class="rvt-dropdown__menu" data-rvt-dropdown-menu hidden>', $html);
        self::assertStringNotContainsString(
            'aria-expanded',
            $html,
            "Rivet's JavaScript adds aria-expanded to a dropdown toggle; authoring it here would conflict.",
        );
    }

    public function testADropdownMenuCanBeRightAligned(): void
    {
        self::assertStringContainsString(
            'class="rvt-dropdown__menu rvt-dropdown__menu--right"',
            new Dropdown(label: 'x', alignRight: true)->render(new RenderContext(), 'y'),
        );
    }

    public function testAnAccordionPanelEmitsNoAriaBecauseRivetGeneratesIt(): void
    {
        $context = new RenderContext();
        $context->open(new Accordion());

        $html = new AccordionPanel(label: 'Panel one')->render($context, '<p>Body</p>');

        self::assertStringContainsString('<button class="rvt-accordion__toggle" type="button" data-rvt-accordion-trigger>', $html);
        self::assertStringNotContainsString(
            'aria-expanded',
            $html,
            'Rivet generates the accordion ARIA and the trigger ids at init; emitting our own would be overwritten or conflict.',
        );
        self::assertStringNotContainsString('aria-labelledby', $html);
    }

    public function testAnAccordionPanelHeadingLevelIsTheCallersChoice(): void
    {
        $context = new RenderContext();
        $context->open(new Accordion());

        self::assertStringContainsString(
            '<h2 class="rvt-accordion__summary">',
            new AccordionPanel(label: 'x', headingLevel: 2)->render($context, 'y'),
            'Where an accordion sits in the document outline is a per-page decision.',
        );
    }

    public function testAnAccordionPanelOutsideAnAccordionIsRejected(): void
    {
        $this->expectException(ComponentContextException::class);

        new AccordionPanel(label: 'x')->render(new RenderContext(), 'y');
    }

    public function testTabsEmitEveryButtonBeforeEveryPanel(): void
    {
        self::assertSame(
            '<div class="rvt-tabs" data-rvt-tabs="rvt-tabs-1">'
            . '<div class="rvt-tabs__tablist" aria-label="Course information" data-rvt-tablist>'
            . '<button class="rvt-tabs__tab" type="button" data-rvt-tab>Description</button>'
            . '<button class="rvt-tabs__tab" type="button" data-rvt-tab>Reviews</button>'
            . '</div>'
            . '<div class="rvt-tabs__panel" data-rvt-tab-panel><p>About</p></div>'
            . '<div class="rvt-tabs__panel" data-rvt-tab-panel data-rvt-tab-init><p>Ratings</p></div>'
            . '</div>',
            $this->tabs(),
            'Rivet pairs tabs to panels by position, so all buttons must precede all panels even though the author writes them together.',
        );
    }

    public function testTabsRequireAnAccessibleName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_tabs requires a label naming the tab list.');

        new Tabs(label: '')->render(new RenderContext(), '');
    }

    public function testATabOutsideATabsComponentIsRejected(): void
    {
        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_tab must be used inside rvt_tabs.');

        new Tab(label: 'x')->render(new RenderContext(), 'y');
    }

    private function tabs(): string
    {
        $context = new RenderContext();
        $tabs = new Tabs(label: 'Course information');
        $context->open($tabs);

        $captured = new Tab(label: 'Description')->render($context, '<p>About</p>')
            . new Tab(label: 'Reviews', openOnInit: true)->render($context, '<p>Ratings</p>');

        $context->close();

        return $tabs->render($context, $captured);
    }
}

<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Sidenav;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sidenav::class)]
final class SidenavTest extends TestCase
{
    public function testTheNavIsNamedByItsOwnLabelElement(): void
    {
        self::assertStringStartsWith(
            '<nav class="rvt-sidenav" aria-labelledby="rvt-sidenav-1-label" data-rvt-sidenav>'
            . '<span class="rvt-sidenav__label" id="rvt-sidenav-1-label">Section pages</span>',
            new Sidenav(label: 'Section pages', items: [['label' => 'One', 'href' => '/one']])
                ->render(new RenderContext()),
            'Two sidenavs on a page would otherwise both be named by the first one.',
        );
    }

    public function testAPlainItemIsALinkInAListItem(): void
    {
        self::assertStringContainsString(
            '<li class="rvt-sidenav__item"><a class="rvt-sidenav__link" href="/one">One</a></li>',
            new Sidenav(label: 'L', items: [['label' => 'One', 'href' => '/one']])->render(new RenderContext()),
        );
    }

    public function testTheCurrentPageIsMarked(): void
    {
        self::assertStringContainsString(
            'aria-current="page"',
            new Sidenav(label: 'L', items: [['label' => 'One', 'href' => '/one', 'current' => true]])
                ->render(new RenderContext()),
        );
    }

    public function testAnItemWithChildrenGetsAToggleAndANestedList(): void
    {
        $html = new Sidenav(label: 'L', items: [[
            'label' => 'Programs',
            'href' => '/programs',
            'children' => [['label' => 'Chemistry', 'href' => '/programs/chem']],
        ]])->render(new RenderContext());

        self::assertStringContainsString('<div class="rvt-sidenav__item-wrapper">', $html);
        self::assertStringContainsString('<button class="rvt-sidenav__toggle" type="button" data-rvt-sidenav-toggle>', $html);
        self::assertStringContainsString(
            '<span class="rvt-sr-only">Show more Programs links</span>',
            $html,
            'An icon-only toggle needs a name, and naming the section it opens is more use than "expand".',
        );
        self::assertStringContainsString('<ul class="rvt-sidenav__list" data-rvt-sidenav-list>', $html);
    }

    public function testNestingDeeperThanRivetSupportsIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sidenav supports at most 4 levels of nesting.');

        $deepest = ['label' => 'Five', 'href' => '/5'];
        $items = [['label' => 'One', 'href' => '/1', 'children' => [
            ['label' => 'Two', 'href' => '/2', 'children' => [
                ['label' => 'Three', 'href' => '/3', 'children' => [
                    ['label' => 'Four', 'href' => '/4', 'children' => [$deepest]],
                ]],
            ]],
        ]]];

        new Sidenav(label: 'L', items: $items)->render(new RenderContext());
    }

    public function testAnItemWithoutALabelIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Every sidenav item needs a "label".');

        new Sidenav(label: 'L', items: [['href' => '/x']])->render(new RenderContext());
    }
}

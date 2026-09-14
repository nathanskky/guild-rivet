<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Alert;
use Guild\Rivet\Enum\AlertStyle;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Alert::class)]
final class AlertTest extends TestCase
{
    public function testAnAlertWiresItsTitleToTheContainerWithAGeneratedId(): void
    {
        self::assertSame(
            '<div class="rvt-alert rvt-alert--info" role="alert" aria-labelledby="rvt-alert-1-title" data-rvt-alert="rvt-alert-1">'
            . '<div class="rvt-alert__title" id="rvt-alert-1-title">Maintenance</div>'
            . '<div class="rvt-alert__message">Back on Tuesday.</div>'
            . '</div>',
            new Alert('Maintenance', dismissible: false)->render(new RenderContext(), 'Back on Tuesday.'),
            'aria-labelledby must reference the title id, and both derive from one generated value.',
        );
    }

    public function testTwoAlertsInOneRenderGetDistinctIds(): void
    {
        $context = new RenderContext();
        $first = new Alert('One', dismissible: false)->render($context, 'a');
        $second = new Alert('Two', dismissible: false)->render($context, 'b');

        self::assertStringContainsString('id="rvt-alert-1-title"', $first);
        self::assertStringContainsString(
            'id="rvt-alert-2-title"',
            $second,
            'Rivet documentation reuses literal ids, so generating distinct ones is the whole point of this library.',
        );
    }

    public function testAnExplicitIdIsUsedInsteadOfAGeneratedOne(): void
    {
        self::assertStringContainsString(
            'data-rvt-alert="maintenance"',
            new Alert('T', id: 'maintenance', dismissible: false)->render(new RenderContext(), 'm'),
            'A caller that needs a stable id for its own JavaScript must be able to set one.',
        );
    }

    public function testTheIdIsResolvedOnlyOnce(): void
    {
        $alert = new Alert('T', dismissible: false);
        $context = new RenderContext();
        $alert->render($context, 'x');

        self::assertStringContainsString(
            'data-rvt-alert="rvt-alert-1"',
            $alert->render($context, 'x'),
            'Re-rendering the same instance must not consume another id and change its markup.',
        );
    }

    public function testTheMessageSlotAcceptsArbitraryBlockContent(): void
    {
        $content = '<p>First.</p><ul class="rvt-list"><li>A point</li></ul>';

        self::assertStringContainsString(
            '<div class="rvt-alert__message">' . $content . '</div>',
            new Alert('T', dismissible: false)->render(new RenderContext(), $content),
            'Rivet documents a single paragraph, but nothing requires one; several paragraphs or a list must produce valid markup, which a <p> wrapper could not.',
        );
    }

    public function testTheTitleIsEscapedButTheContentIsNot(): void
    {
        $html = new Alert('Fish & Chips', dismissible: false)->render(new RenderContext(), '<p>Already rendered</p>');

        self::assertStringContainsString('>Fish &amp; Chips<', $html, 'The title is caller text and is escaped.');
        self::assertStringContainsString('<p>Already rendered</p>', $html, 'Content arrives as markup the engine already rendered.');
    }

    public function testADismissibleAlertCarriesTheDismissButtonAndItsAccessibleName(): void
    {
        $html = new Alert('T')->render(new RenderContext(), 'm');

        self::assertStringContainsString('<button class="rvt-alert__dismiss" type="button" data-rvt-alert-close>', $html);
        self::assertStringContainsString('<span class="rvt-sr-only">Dismiss this alert</span>', $html, 'An icon-only control needs a screen-reader name.');
        self::assertStringContainsString('<svg ', $html, 'Component chrome embeds its own SVG so the alert works without the rivet-icons package.');
    }

    public function testTheDangerStyleChangesOnlyTheModifier(): void
    {
        self::assertStringContainsString(
            'class="rvt-alert rvt-alert--danger"',
            new Alert('T', style: AlertStyle::Danger, dismissible: false)->render(new RenderContext(), 'm'),
            'Style maps to exactly one modifier class.',
        );
    }
}

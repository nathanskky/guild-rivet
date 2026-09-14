<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Card\Card;
use Guild\Rivet\Component\Card\CardBody;
use Guild\Rivet\Component\Card\CardImage;
use Guild\Rivet\Exception\ComponentContextException;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Card::class)]
#[CoversClass(CardBody::class)]
#[CoversClass(CardImage::class)]
final class CardTest extends TestCase
{
    public function testACardWrapsItsContent(): void
    {
        self::assertSame(
            '<div class="rvt-card rvt-card--raised">inner</div>',
            new Card(raised: true)->render(new RenderContext(), 'inner'),
            'Raised, horizontal and clickable are independent boolean modifiers.',
        );
    }

    public function testACardBodyRendersTitleEyebrowContentAndMeta(): void
    {
        self::assertSame(
            '<div class="rvt-card__body">'
            . '<div class="rvt-card__eyebrow">Category</div>'
            . '<h2 class="rvt-card__title"><a href="/life">Campus life</a></h2>'
            . '<div class="rvt-card__content"><p>Body</p></div>'
            . '<div class="rvt-card__meta">November 5</div>'
            . '</div>',
            $this->insideCard(new CardBody(
                title: 'Campus life',
                titleHref: '/life',
                eyebrow: 'Category',
                meta: 'November 5',
            ), '<p>Body</p>'),
            'Rivet fixes the order of the card body parts.',
        );
    }

    public function testATitleWithoutALinkIsRenderedPlainly(): void
    {
        self::assertStringContainsString(
            '<h2 class="rvt-card__title">Campus life</h2>',
            $this->insideCard(new CardBody(title: 'Campus life'), 'x'),
            'A non-clickable card may have a title that is not a link.',
        );
    }

    public function testTheHeadingLevelIsTheCallersChoice(): void
    {
        self::assertStringContainsString(
            '<h3 class="rvt-card__title">',
            $this->insideCard(new CardBody(title: 'T', headingLevel: 3), 'x'),
            'Where a card sits in the document outline is a per-page decision.',
        );
    }

    public function testAnOutOfRangeHeadingLevelIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Heading level must be between 1 and 6, 7 given.');

        $this->insideCard(new CardBody(title: 'T', headingLevel: 7), 'x');
    }

    public function testAClickableCardRequiresItsTitleToBeALink(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A clickable card needs a titleHref: Rivet makes the card clickable by expanding the title link.');

        $context = new RenderContext();
        $card = new Card(clickable: true);
        $context->open($card);
        new CardBody(title: 'No link')->render($context, 'x');
    }

    public function testACardBodyOutsideACardIsRejected(): void
    {
        $this->expectException(ComponentContextException::class);
        $this->expectExceptionMessage('rvt_card_body must be used inside rvt_card.');

        new CardBody(title: 'T')->render(new RenderContext(), 'x');
    }

    public function testACardImageOutsideACardIsRejected(): void
    {
        $this->expectException(ComponentContextException::class);

        new CardImage(src: '/a.webp', alt: '')->render(new RenderContext());
    }

    public function testACardImageRendersItsFigure(): void
    {
        self::assertSame(
            '<div class="rvt-card__image"><img src="/a.webp" alt=""></div>',
            $this->insideCard(new CardImage(src: '/a.webp', alt: '')),
            'An empty alt marks a decorative image, which is what a card illustration usually is.',
        );
    }

    private function insideCard(CardBody|CardImage $component, string $content = ''): string
    {
        $context = new RenderContext();
        $context->open(new Card());

        return $component->render($context, $content);
    }
}

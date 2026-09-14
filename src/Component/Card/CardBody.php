<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Card;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * The text half of a card: eyebrow, title, content and meta, in Rivet's fixed order.
 *
 * @see https://rivet.iu.edu/components/card/
 */
final class CardBody extends Component
{
    private const string BLOCK = 'rvt-card';

    public function __construct(
        private readonly string $title,
        private readonly ?string $titleHref = null,
        private readonly int $headingLevel = 2,
        private readonly ?string $eyebrow = null,
        private readonly ?string $meta = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_card_body';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $card = $context->requireAncestor(Card::class, self::class);

        if ($this->headingLevel < 1 || $this->headingLevel > 6) {
            throw new InvalidArgumentException(sprintf(
                'Heading level must be between 1 and 6, %d given.',
                $this->headingLevel,
            ));
        }

        // Rivet makes a clickable card work by expanding the title's anchor to cover the
        // card, so a clickable card with no link has nothing to expand.
        if ($card->isClickable() && ($this->titleHref === null || $this->titleHref === '')) {
            throw new InvalidArgumentException(
                'A clickable card needs a titleHref: Rivet makes the card clickable by expanding the title link.',
            );
        }

        return Html::el('div')
            ->class(self::BLOCK . '__body')
            ->merge($this->extra)
            ->children(
                $this->eyebrow === null
                    ? null
                    : Html::el('div')->class(self::BLOCK . '__eyebrow')->text($this->eyebrow),
                $this->heading(),
                $content === ''
                    ? null
                    : Html::el('div')->class(self::BLOCK . '__content')->html($content),
                $this->meta === null
                    ? null
                    : Html::el('div')->class(self::BLOCK . '__meta')->text($this->meta),
            )
            ->render();
    }

    private function heading(): Html
    {
        $heading = Html::el('h' . $this->headingLevel)->class(self::BLOCK . '__title');

        return $this->titleHref === null || $this->titleHref === ''
            ? $heading->text($this->title)
            : $heading->children(Html::el('a')->attr('href', $this->titleHref)->text($this->title));
    }
}

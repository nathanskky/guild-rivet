<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Card;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * The illustration slot of a card.
 *
 * @see https://rivet.iu.edu/components/card/
 */
final class CardImage extends Component
{
    public function __construct(
        private readonly string $src,
        private readonly string $alt,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_card_image';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $context->requireAncestor(Card::class, self::class);

        return Html::el('div')
            ->class('rvt-card__image')
            ->merge($this->extra)
            ->children(
                Html::el('img')->attr('src', $this->src)->attr('alt', $this->alt),
            )
            ->render();
    }
}

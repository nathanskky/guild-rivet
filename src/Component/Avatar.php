<?php

declare(strict_types=1);

namespace Guild\Rivet\Component;

use Guild\Rivet\Enum\AvatarSize;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet avatar — a photograph, or initials as the fallback.
 *
 * @see https://rivet.iu.edu/components/avatar/
 */
final class Avatar extends Component
{
    private const string BLOCK = 'rvt-avatar';

    private const int MAX_INITIALS = 2;

    public function __construct(
        private readonly ?string $src = null,
        private readonly ?string $alt = null,
        private readonly ?string $initials = null,
        private readonly AvatarSize $size = AvatarSize::Default,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_avatar';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        return Html::el('div')
            ->class(
                self::BLOCK,
                $this->size === AvatarSize::Default ? null : self::BLOCK . '--' . $this->size->value,
            )
            ->merge($this->extra)
            ->children($this->inner())
            ->render();
    }

    private function inner(): Html
    {
        if ($this->src !== null && $this->src !== '') {
            if ($this->alt === null) {
                throw new InvalidArgumentException(self::name() . ' requires alt text describing the image.');
            }

            return Html::el('img')
                ->class(self::BLOCK . '__image')
                ->attr('src', $this->src)
                ->attr('alt', $this->alt);
        }

        if ($this->initials === null || $this->initials === '') {
            throw new InvalidArgumentException(self::name() . ' needs either src or initials.');
        }

        if (mb_strlen($this->initials) > self::MAX_INITIALS) {
            throw new InvalidArgumentException(sprintf(
                'An avatar takes at most %d initials, "%s" given.',
                self::MAX_INITIALS,
                $this->initials,
            ));
        }

        return Html::el('span')->class(self::BLOCK . '__text')->text($this->initials);
    }
}

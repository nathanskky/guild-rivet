<?php

declare(strict_types=1);

namespace Guild\Rivet\Test\Component;

use Guild\Rivet\Component\Avatar;
use Guild\Rivet\Enum\AvatarSize;
use Guild\Rivet\Exception\InvalidArgumentException;
use Guild\Rivet\Render\RenderContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Avatar::class)]
final class AvatarTest extends TestCase
{
    public function testInitialsRenderAsText(): void
    {
        self::assertSame(
            '<div class="rvt-avatar"><span class="rvt-avatar__text">NR</span></div>',
            new Avatar(initials: 'NR')->render(new RenderContext()),
            'The initials form is the fallback when there is no photograph.',
        );
    }

    public function testAnImageRendersWithItsAlternativeText(): void
    {
        self::assertSame(
            '<div class="rvt-avatar rvt-avatar--lg"><img class="rvt-avatar__image" src="/me.jpg" alt="Nathan"></div>',
            new Avatar(src: '/me.jpg', alt: 'Nathan', size: AvatarSize::Large)->render(new RenderContext()),
            'An avatar image needs alternative text naming the person.',
        );
    }

    public function testMoreThanTwoInitialsAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An avatar takes at most 2 initials, "NSR" given.');

        new Avatar(initials: 'NSR')->render(new RenderContext());
    }

    public function testAnAvatarWithNeitherImageNorInitialsIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_avatar needs either src or initials.');

        new Avatar()->render(new RenderContext());
    }

    public function testAnImageWithoutAlternativeTextIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rvt_avatar requires alt text describing the image.');

        new Avatar(src: '/me.jpg')->render(new RenderContext());
    }
}

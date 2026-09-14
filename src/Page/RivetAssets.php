<?php

declare(strict_types=1);

namespace Guild\Rivet\Page;

use Guild\Rivet\Rivet;

/**
 * Where a page finds Rivet's own stylesheet and script.
 *
 * Held apart from PageDefaults so that object stays readable, and so a self-hosted
 * application can point at its own files rather than disabling the tags altogether.
 * A version is named once here and flows into every URL.
 *
 * Deliberately emits no `integrity` attribute on any of the CDN tags. A Subresource
 * Integrity hash is tied to one exact file, `version` and `iconsVersion` here are
 * caller-configurable, and a hash that no longer matches the file it names blocks the
 * asset from loading at all rather than degrading. An application that needs a hardened
 * supply chain should self-host through `cssHref`/`jsSrc` (and their icons equivalents)
 * and add its own integrity hash to the tag it controls.
 */
final readonly class RivetAssets
{
    private const string CDN = 'https://unpkg.com';

    public function __construct(
        public bool $enabled = true,
        public bool $icons = true,
        public string $version = Rivet::VERSION,
        public string $iconsVersion = '3.0.1',
        public ?string $cssHref = null,
        public ?string $jsSrc = null,
        public ?string $iconsCssHref = null,
        public ?string $iconsJsSrc = null,
    ) {
    }

    public function coreCss(): string
    {
        return $this->cssHref ?? self::CDN . '/rivet-core@' . $this->version . '/css/rivet.min.css';
    }

    public function coreJs(): string
    {
        return $this->jsSrc ?? self::CDN . '/rivet-core@' . $this->version . '/js/rivet.min.js';
    }

    public function iconsCss(): string
    {
        return $this->iconsCssHref
            ?? self::CDN . '/rivet-icons@' . $this->iconsVersion . '/dist/rivet-icon-element.css';
    }

    public function iconsJs(): string
    {
        return $this->iconsJsSrc
            ?? self::CDN . '/rivet-icons@' . $this->iconsVersion . '/dist/rivet-icons.js';
    }
}

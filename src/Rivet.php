<?php

declare(strict_types=1);

namespace Guild\Rivet;

use Guild\Rivet\Component\Alert;
use Guild\Rivet\Component\Avatar;
use Guild\Rivet\Component\Badge;
use Guild\Rivet\Component\Breadcrumbs;
use Guild\Rivet\Component\Button;
use Guild\Rivet\Component\ButtonGroup;
use Guild\Rivet\Component\Card\Card;
use Guild\Rivet\Component\Card\CardBody;
use Guild\Rivet\Component\Card\CardImage;
use Guild\Rivet\Component\Grid\Column;
use Guild\Rivet\Component\Grid\Container;
use Guild\Rivet\Component\Grid\Row;
use Guild\Rivet\Component\Icon;
use Guild\Rivet\Component\InlineAlert;
use Guild\Rivet\Component\ListComponent;
use Guild\Rivet\Component\LoadingIndicator;
use Guild\Rivet\Component\Pagination;
use Guild\Rivet\Component\SegmentedButtons;
use Guild\Rivet\Component\Table;
use Guild\Rivet\Render\ComponentRegistry;

/**
 * Entry point for the component set.
 */
final class Rivet
{
    /**
     * The Rivet release whose markup this library emits.
     *
     * Rivet 3 is in development and keeps the `rvt-` classes and every `data-rvt-*`
     * attribute, so the identifier wiring here carries over; the button modifiers and a
     * few block names do change. Treat a `beta` tag on `@rivet-iu/core` as the signal to
     * revisit.
     */
    public const string VERSION = '2.9.1';

    /**
     * Every component this package ships.
     */
    public static function registry(): ComponentRegistry
    {
        return new ComponentRegistry([
            Alert::class,
            Avatar::class,
            Badge::class,
            Breadcrumbs::class,
            Button::class,
            ButtonGroup::class,
            Card::class,
            CardBody::class,
            CardImage::class,
            Column::class,
            Container::class,
            Icon::class,
            InlineAlert::class,
            ListComponent::class,
            LoadingIndicator::class,
            Pagination::class,
            Row::class,
            SegmentedButtons::class,
            Table::class,
        ]);
    }
}

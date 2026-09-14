<?php

declare(strict_types=1);

namespace Guild\Rivet;

use Guild\Rivet\Component\Alert;
use Guild\Rivet\Component\Avatar;
use Guild\Rivet\Component\Badge;
use Guild\Rivet\Component\Breadcrumbs;
use Guild\Rivet\Component\Button;
use Guild\Rivet\Component\ButtonGroup;
use Guild\Rivet\Component\Accordion\Accordion;
use Guild\Rivet\Component\Accordion\AccordionPanel;
use Guild\Rivet\Component\Card\Card;
use Guild\Rivet\Component\Card\CardBody;
use Guild\Rivet\Component\Card\CardImage;
use Guild\Rivet\Component\Dialog\Dialog;
use Guild\Rivet\Component\Dialog\DialogBody;
use Guild\Rivet\Component\Dialog\DialogControls;
use Guild\Rivet\Component\Disclosure;
use Guild\Rivet\Component\Dropdown;
use Guild\Rivet\Component\Form\Checkbox;
use Guild\Rivet\Component\Form\FieldGroup;
use Guild\Rivet\Component\Form\FileInput;
use Guild\Rivet\Component\Form\FormField;
use Guild\Rivet\Component\Form\InputGroup;
use Guild\Rivet\Component\Form\InputGroupAddon;
use Guild\Rivet\Component\Form\Radio;
use Guild\Rivet\Component\Form\Select;
use Guild\Rivet\Component\Form\Textarea;
use Guild\Rivet\Component\Form\TextInput;
use Guild\Rivet\Component\Form\ToggleSwitch;
use Guild\Rivet\Component\Grid\Column;
use Guild\Rivet\Component\Grid\Container;
use Guild\Rivet\Component\Grid\Row;
use Guild\Rivet\Component\Icon;
use Guild\Rivet\Component\InlineAlert;
use Guild\Rivet\Component\ListComponent;
use Guild\Rivet\Component\LoadingIndicator;
use Guild\Rivet\Component\Pagination;
use Guild\Rivet\Component\SegmentedButtons;
use Guild\Rivet\Component\Sidenav;
use Guild\Rivet\Component\Table;
use Guild\Rivet\Component\Tabs\Tab;
use Guild\Rivet\Component\Tabs\Tabs;
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
            Accordion::class,
            AccordionPanel::class,
            Alert::class,
            Avatar::class,
            Badge::class,
            Breadcrumbs::class,
            Button::class,
            ButtonGroup::class,
            Card::class,
            Checkbox::class,
            Dialog::class,
            DialogBody::class,
            DialogControls::class,
            Disclosure::class,
            Dropdown::class,
            CardBody::class,
            CardImage::class,
            Column::class,
            Container::class,
            FieldGroup::class,
            FileInput::class,
            FormField::class,
            Icon::class,
            InlineAlert::class,
            InputGroup::class,
            InputGroupAddon::class,
            ListComponent::class,
            LoadingIndicator::class,
            Pagination::class,
            Radio::class,
            Row::class,
            Select::class,
            SegmentedButtons::class,
            Sidenav::class,
            Tab::class,
            Table::class,
            Tabs::class,
            Textarea::class,
            TextInput::class,
            ToggleSwitch::class,
        ]);
    }
}

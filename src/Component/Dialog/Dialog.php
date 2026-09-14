<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Dialog;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Enum\DialogPosition;
use Guild\Rivet\Enum\DialogVariant;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet dialog.
 *
 * The most identifier-dependent component in the system: the same string appears on the
 * dialog's `id`, its `data-rvt-dialog`, the trigger that opens it, every close button,
 * and — as derived values — the title it is labelled by and the body it is described by.
 * Rivet's documentation hard-codes all of these, so two dialogs copied from it onto one
 * page share a title and a trigger. Here they all derive from one generated value.
 *
 * The trigger is rendered by this component rather than written separately, because it
 * must know the identifier before the dialog exists.
 *
 * Behaviour flags are nullable: null means "whatever this variant implies", and a stated
 * value is the caller overriding it.
 *
 * A dialog is expected to contain a DialogBody, which supplies the element named by
 * `aria-describedby`.
 *
 * @see https://rivet.iu.edu/components/dialog/
 */
final class Dialog extends Component
{
    private const string BLOCK = 'rvt-dialog';

    public function __construct(
        private readonly string $title,
        private readonly DialogVariant $variant = DialogVariant::Default,
        private readonly ?DialogPosition $position = null,
        private readonly ?bool $modal = null,
        private readonly ?bool $darkenPage = null,
        private readonly ?bool $disablePageInteraction = null,
        private readonly ?bool $openOnInit = null,
        private readonly bool $showClose = true,
        private readonly ?string $triggerText = null,
        private readonly string $closeLabel = 'Close',
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_dialog';
    }

    public static function acceptsContent(): bool
    {
        return true;
    }

    /**
     * The identifier every part of this dialog is wired with.
     */
    public function dialogId(RenderContext $context): string
    {
        return $this->resolveId($context, self::BLOCK, $this->id);
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $id = $this->dialogId($context);
        $isHelpWidget = $this->variant === DialogVariant::HelpWidget;
        $position = $this->position ?? $this->defaultPosition();

        $dialog = Html::el('div')
            ->class(self::BLOCK)
            ->attr('id', $id)
            ->attr('role', 'dialog')
            ->attr('tabindex', '-1')
            // A help widget has no title bar, so it is named directly instead of
            // pointing at a title element that does not exist.
            ->attr('aria-labelledby', $isHelpWidget ? null : $id . '-title')
            ->attr('aria-label', $isHelpWidget ? $this->title : null)
            ->attr('aria-describedby', $id . '-description')
            ->attr('data-rvt-dialog', $id)
            ->attr('data-rvt-dialog-modal', $this->flag($this->modal, 'modal'))
            ->attr('data-rvt-dialog-darken-page', $this->flag($this->darkenPage, 'darken'))
            ->attr('data-rvt-dialog-disable-page-interaction', $this->flag($this->disablePageInteraction, 'disable'))
            ->attr('data-rvt-dialog-open-on-init', $this->flag($this->openOnInit, 'open'))
            ->attr(
                $position === DialogPosition::Default ? 'data-rvt-dialog-position' : 'data-rvt-dialog-' . $position->value,
                $position === DialogPosition::Default ? null : true,
            )
            ->attr('hidden', true)
            ->merge($this->extra)
            ->children($isHelpWidget ? null : $this->header($id))
            ->html($content)
            ->children($this->showClose ? $this->closeButton($id) : null);

        return $this->trigger($id) . $dialog->render();
    }

    private function header(string $id): Html
    {
        return Html::el('header')
            ->class(self::BLOCK . '__header')
            ->children(
                Html::el('h1')->class(self::BLOCK . '__title')->attr('id', $id . '-title')->text($this->title),
            );
    }

    private function closeButton(string $id): Html
    {
        return Html::el('button')
            ->class('rvt-button', 'rvt-button--plain', self::BLOCK . '__close')
            ->attr('type', 'button')
            ->attr('data-rvt-dialog-close', $id)
            ->children(
                Html::el('span')->class('rvt-sr-only')->text($this->closeLabel),
                SvgIcon::render('close'),
            );
    }

    private function trigger(string $id): string
    {
        if ($this->triggerText === null || $this->triggerText === '') {
            return '';
        }

        return Html::el('button')
            ->class('rvt-button')
            ->attr('type', 'button')
            ->attr('data-rvt-dialog-trigger', $id)
            ->children(Html::el('span')->text($this->triggerText))
            ->render();
    }

    /**
     * Resolve one behaviour flag: the caller's answer, or the variant's.
     */
    private function flag(?bool $explicit, string $behaviour): ?bool
    {
        $value = $explicit ?? $this->variantDefault($behaviour);

        return $value ? true : null;
    }

    private function variantDefault(string $behaviour): bool
    {
        return match ($this->variant) {
            // Dialogs that demand an answer take over the page.
            DialogVariant::Modal, DialogVariant::Confirmation => $behaviour !== 'open',
            // Ones that volunteer information sit quietly in a corner.
            DialogVariant::Notification, DialogVariant::HelpWidget => $behaviour === 'open',
            // The plain dialog blocks the page behind it without dimming it.
            DialogVariant::Default => $behaviour === 'disable',
        };
    }

    private function defaultPosition(): DialogPosition
    {
        return match ($this->variant) {
            DialogVariant::Notification => DialogPosition::TopRight,
            DialogVariant::HelpWidget => DialogPosition::BottomRight,
            default => DialogPosition::Default,
        };
    }
}

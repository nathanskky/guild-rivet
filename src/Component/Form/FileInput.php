<?php

declare(strict_types=1);

namespace Guild\Rivet\Component\Form;

use Guild\Rivet\Component\Component;
use Guild\Rivet\Component\Internal\SvgIcon;
use Guild\Rivet\Html\Attributes;
use Guild\Rivet\Html\Html;
use Guild\Rivet\Render\RenderContext;

/**
 * Rivet file input.
 *
 * One identifier appears five times here — on the wrapper, the input, the label's `for`,
 * the input's `aria-describedby`, and the preview that Rivet's JavaScript writes the
 * chosen filename into. Rivet's documentation hard-codes both that identifier and the
 * preview id, so two file inputs copied from the docs onto one page send every filename
 * to the first one's preview. All five derive from a single value here.
 *
 * The visible control is the label, styled as a button; the real input sits behind it.
 *
 * @see https://rivet.iu.edu/components/file-input/
 */
final class FileInput extends Component
{
    private const string BLOCK = 'rvt-file';

    public function __construct(
        private readonly string $name,
        private readonly string $label = 'Upload a file',
        private readonly string $emptyText = 'No file selected',
        private readonly bool $multiple = false,
        private readonly bool $disabled = false,
        private readonly ?string $accept = null,
        private readonly ?string $id = null,
        private readonly Attributes $extra = new Attributes(),
    ) {
    }

    public static function name(): string
    {
        return 'rvt_file_input';
    }

    public function render(RenderContext $context, string $content = ''): string
    {
        $id = $this->resolveId($context, self::BLOCK, $this->id);
        $previewId = $id . '-description';

        return Html::el('div')
            ->class(self::BLOCK)
            ->attr('data-rvt-file-input', $id)
            ->merge($this->extra)
            ->children(
                Html::el('input')
                    ->attr('type', 'file')
                    ->attr('id', $id)
                    ->attr('name', $this->name)
                    ->attr('data-rvt-file-input-button', $id)
                    ->attr('aria-describedby', $previewId)
                    ->attr('accept', $this->accept)
                    ->attr('multiple', $this->multiple)
                    ->attr('disabled', $this->disabled),
                Html::el('label')
                    ->class('rvt-button')
                    ->attr('for', $id)
                    ->children(
                        Html::el('span')->text($this->label),
                        SvgIcon::render('file'),
                    ),
                Html::el('div')
                    ->class(self::BLOCK . '__preview')
                    ->attr('id', $previewId)
                    ->attr('data-rvt-file-input-preview', $id)
                    ->text($this->emptyText),
            )
            ->render();
    }
}

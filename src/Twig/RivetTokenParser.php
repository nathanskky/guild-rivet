<?php

declare(strict_types=1);

namespace Guild\Rivet\Twig;

use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Parses one component tag. One instance is registered per component.
 *
 * Arguments are written as `name=value` or `name: value`, with commas optional, so both
 * Twig's own conventions read naturally. Names are snake_case: Twig lexes `data-foo` as
 * three separate tokens, so a hyphen cannot appear in tag syntax at all, and the
 * renderer maps underscores back to hyphens.
 */
final class RivetTokenParser extends AbstractTokenParser
{
    public function __construct(
        private readonly string $component,
        private readonly bool $acceptsContent,
    ) {
    }

    public function getTag(): string
    {
        return $this->component;
    }

    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();
        $line = $token->getLine();
        $arguments = $this->parseArguments();

        if (! $this->acceptsContent) {
            return new RivetLeafNode($this->component, $arguments, $line);
        }

        $body = $this->parser->subparse(
            fn (Token $token): bool => $token->test('end' . $this->component),
            true,
        );
        $stream->expect(Token::BLOCK_END_TYPE);

        return new RivetBlockNode($this->component, $arguments, $body, $line);
    }

    /**
     * Collect `name=value` pairs up to the end of the tag, into an array expression.
     *
     * A tag with no arguments falls straight through and yields an empty array.
     */
    private function parseArguments(): ArrayExpression
    {
        $stream = $this->parser->getStream();
        $arguments = new ArrayExpression([], $stream->getCurrent()->getLine());

        while (! $stream->test(Token::BLOCK_END_TYPE)) {
            $name = $stream->expect(Token::NAME_TYPE);

            if (! $stream->nextIf(Token::OPERATOR_TYPE, '=')) {
                $stream->expect(Token::PUNCTUATION_TYPE, ':');
            }

            $arguments->addElement(
                $this->parser->parseExpression(),
                new ConstantExpression($name->getValue(), $name->getLine()),
            );

            $stream->nextIf(Token::PUNCTUATION_TYPE, ',');
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return $arguments;
    }
}

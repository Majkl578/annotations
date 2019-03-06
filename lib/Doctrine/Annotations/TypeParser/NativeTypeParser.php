<?php

declare(strict_types=1);

namespace Doctrine\Annotations\TypeParser;

use Doctrine\Annotations\Type\Type;
use Hoa\Compiler\Llk\Llk;
use Hoa\Compiler\Llk\Parser;
use Hoa\Compiler\Llk\TreeNode;
use Hoa\File\Read;
use function preg_match;

/**
 * @internal
 */
final class NativeTypeParser implements TypeParser
{
    private const ROOT_RULE = 'type';

    /** @var Parser */
    private $compiler;

    /** @var TypeVisitor */
    private $visitor;

    public function __construct()
    {
        $this->compiler = Llk::load(new Read(__DIR__ . '/type.pp'));
        $this->visitor  = new TypeVisitor();
    }

    /**
     * @param array<string, string> $imports
     */
    public function parsePropertyType(string $docBlock, array $imports) : Type
    {
        preg_match('~@var\s+(.+)$~', $docBlock, $match);

        /** @var TreeNode $trace */
        $tree = $this->compiler->parse($match[1], self::ROOT_RULE);

        return $this->visitor->visit($tree);
    }
}

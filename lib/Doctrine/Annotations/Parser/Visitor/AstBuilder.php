<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Parser\Visitor;

use Doctrine\Annotations\Parser\Ast\Annotation;
use Doctrine\Annotations\Parser\Ast\Annotations;
use Doctrine\Annotations\Parser\Ast\ClassConstantFetch;
use Doctrine\Annotations\Parser\Ast\Collection\Collection;
use Doctrine\Annotations\Parser\Ast\Collection\Entry;
use Doctrine\Annotations\Parser\Ast\Collection\NamedEntry;
use Doctrine\Annotations\Parser\Ast\Collection\PositionalEntry;
use Doctrine\Annotations\Parser\Ast\ConstantFetch;
use Doctrine\Annotations\Parser\Ast\Node;
use Doctrine\Annotations\Parser\Ast\Pair;
use Doctrine\Annotations\Parser\Ast\Parameter\NamedParameter;
use Doctrine\Annotations\Parser\Ast\Parameter\UnnamedParameter;
use Doctrine\Annotations\Parser\Ast\Parameters;
use Doctrine\Annotations\Parser\Ast\Reference;
use Doctrine\Annotations\Parser\Ast\Scalar;
use Doctrine\Annotations\Parser\Ast\Scalar\BooleanScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\FloatScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\Identifier;
use Doctrine\Annotations\Parser\Ast\Scalar\IntegerScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\NullScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\StringScalar;
use Doctrine\Annotations\Parser\Nodes;
use Doctrine\Annotations\Parser\Tokens;
use Hoa\Compiler\Llk\TreeNode;
use Hoa\Visitor\Element;
use Hoa\Visitor\Visit;
use function assert;
use function ltrim;
use function sprintf;
use function str_replace;
use function strcasecmp;

final class AstBuilder implements Visit
{
    /**
     * @param mixed $handle
     * @param mixed $eldnah
     */
    public function visit(Element $node, &$handle = null, $eldnah = null) : Node
    {
        assert($node instanceof TreeNode);

        if ($node->isToken()) {
            return $this->visitToken($node);
        }

        return $this->visitNode($node);
    }

    private function visitNode(TreeNode $node) : Node
    {
        switch ($node->getId()) {
            case Nodes::ANNOTATIONS:
                return $this->visitAnnotations($node);
            case Nodes::ANNOTATION:
                return $this->visitAnnotation($node);
            case Nodes::COLLECTION:
                return $this->visitCollection($node);
            case Nodes::COLLECTION_ENTRY:
                return $this->visitCollectionEntry($node);
            case Nodes::PAIR:
                return $this->visitPair($node);
            case Nodes::PARAMETERS:
                return $this->visitParameters($node);
            case Nodes::NAMED_PARAMETERS:
                return $this->visitNamedParameter($node);
            case Nodes::UNNAMED_PARAMETERS:
                return $this->visitUnnamedParameter($node);
            case Nodes::VALUE:
                return $this->visitValue($node);
            case Nodes::STANDALONE_CONSTANT:
                return $this->visitStandaloneConstant($node);
            case Nodes::CLASS_CONSTANT:
                return $this->visitClassConstant($node);
            case Nodes::REFERENCE:
                return $this->visitReference($node);
            case Nodes::STRING:
                return $this->visitString($node);
        }

        assert(false, sprintf('Unsupported node %s.', $node->getId()));
    }

    private function visitToken(TreeNode $node) : Scalar
    {
        $value = $node->getValueValue();

        switch ($node->getValueToken()) {
            case Tokens::IDENTIFIER:
                return new Identifier($value);
            case Tokens::NULL:
                return new NullScalar();
            case Tokens::BOOLEAN:
                return new BooleanScalar(strcasecmp($value, 'true') === 0);
            case Tokens::INTEGER:
                $intValue = (int) $value;
                assert((string) $intValue === $value, 'Integer overflow');

                return new IntegerScalar($intValue);
            case Tokens::FLOAT:
                return new FloatScalar((float) $value);
        }

        assert(false, sprintf('Unsupported token %s.', $node->getValueToken()));
    }

    private function visitAnnotations(TreeNode $node) : Annotations
    {
        return new Annotations(
            ...(function (TreeNode ...$nodes) : iterable {
                foreach ($nodes as $node) {
                    yield $node->accept($this);
                }
            })(...$node->getChildren())
        );
    }

    private function visitAnnotation(TreeNode $node) : Annotation
    {
        $identifier = $node->getChild(0)->getValueValue();

        return new Annotation(
            new Reference(ltrim($identifier, '\\'), $identifier[0] === '\\'),
            $node->childExists(1) ? $node->getChild(1)->accept($this) : new Parameters()
        );
    }

    private function visitPair(TreeNode $node) : Pair
    {
        return new Pair(
            $node->getChild(0)->accept($this),
            $node->getChild(1)->accept($this)
        );
    }

    private function visitParameters(TreeNode $node) : Parameters
    {
        return new Parameters(
            ...(function (TreeNode ...$nodes) : iterable {
                foreach ($nodes as $node) {
                    yield $node->accept($this);
                }
            })(...$node->getChildren())
        );
    }

    private function visitNamedParameter(TreeNode $node) : NamedParameter
    {
        return new NamedParameter(
            $node->getChild(0)->accept($this),
            $node->getChild(1)->accept($this)
        );
    }

    private function visitUnnamedParameter(TreeNode $node) : UnnamedParameter
    {
        return new UnnamedParameter($node->getChild(0)->accept($this));
    }

    private function visitValue(TreeNode $node) : Node
    {
        return $node->getChild(0)->accept($this);
    }

    private function visitCollection(TreeNode $node) : Collection
    {
        return new Collection(
            ...(function (TreeNode ...$nodes) : iterable {
                foreach ($nodes as $node) {
                    yield $node->accept($this);
                }
            })(...$node->getChildren())
        );
    }

    private function visitCollectionEntry(TreeNode $node) : Entry
    {
        if ($node->getChildrenNumber() === 1) {
            return new PositionalEntry($node->getChild(0)->accept($this));
        }

        return new NamedEntry(
            $node->getChild(0)->accept($this),
            $node->getChild(1)->accept($this)
        );
    }

    private function visitStandaloneConstant(TreeNode $node) : ConstantFetch
    {
        return new ConstantFetch($node->getChild(0)->accept($this));
    }

    private function visitClassConstant(TreeNode $node) : ClassConstantFetch
    {
        return new ClassConstantFetch(
            $node->getChild(0)->accept($this),
            $node->getChild(1)->accept($this)
        );
    }

    private function visitReference(TreeNode $node) : Reference
    {
        $child = $node->getChild(0);
        $value = $child->getValueValue();

        if ($child->getValueToken() === Tokens::NAMESPACED_IDENTIFIER && $value[0] === '\\') {
            return new Reference(ltrim($value, '\\'), true);
        }

        return new Reference($value, false);
    }

    private function visitString(TreeNode $node) : StringScalar
    {
        return new StringScalar(str_replace('\\\\', '\\', $node->getChild(0) ? $node->getChild(0)->getValueValue() : ''));
    }
}

<?php

declare(strict_types=1);

namespace Doctrine\Annotations\TypeParser;

use Doctrine\Annotations\Type\BooleanType;
use Doctrine\Annotations\Type\Constant\BooleanType as ConstantBooleanType;
use Doctrine\Annotations\Type\FloatType;
use Doctrine\Annotations\Type\IntegerType;
use Doctrine\Annotations\Type\IntersectionType;
use Doctrine\Annotations\Type\ListType;
use Doctrine\Annotations\Type\MapType;
use Doctrine\Annotations\Type\MixedType;
use Doctrine\Annotations\Type\NullType;
use Doctrine\Annotations\Type\ObjectType;
use Doctrine\Annotations\Type\StringType;
use Doctrine\Annotations\Type\Type;
use Doctrine\Annotations\Type\UnionType;
use Hoa\Compiler\Llk\TreeNode;
use Hoa\Visitor\Element;
use Hoa\Visitor\Visit;
use function array_map;
use function assert;

final class TypeVisitor implements Visit
{
    public function visit(Element $element, &$handle = null, $eldnah = null) : Type
    {
        assert($element instanceof TreeNode);

        if ($element->getId() === Nodes::NULL) {
            return new NullType();
        }

        if ($element->getId() === Nodes::BOOLEAN) {
            if ($element->getChildrenNumber() === 0) {
                return new BooleanType();
            }

            return new ConstantBooleanType($element->getChild(0)->getValueValue() === 'true');
        }

        if ($element->getId() === Nodes::INTEGER) {
            return new IntegerType();
        }

        if ($element->getId() === Nodes::FLOAT) {
            return new FloatType();
        }

        if ($element->getId() === Nodes::STRING) {
            return new StringType();
        }

        if ($element->getId() === Nodes::ITERABLE) {
            if ($element->getChildrenNumber() === 0) {
                return new IterableType();
            }

            $generic = $element->getChild(0);

            // TODO
        }

        if ($element->getId() === Nodes::CALLABLE) {
            return new CallableType();
        }

        if ($element->getId() === Nodes::OBJECT) {
            if ($element->getChildrenNumber() === 0) {
                return new ObjectType(null);
            }

            if ($element->getChildrenNumber() === 1) {
                return new ObjectType($element->getChild(0)->getValueValue());
            }

            // TODO generic
        }

        if ($element->getId() === Nodes::ARRAY) {
            if ($element->getChildrenNumber() === 0) {
                return new MapType(new UnionType(new IntegerType(), new StringType()), new MixedType());
            }

            $generic = $element->getChild(0);
            assert($generic->getChildrenNumber() === 1 || $generic->getChildrenNumber() === 2);

            if ($generic->getChildrenNumber() === 2) {
                return new MapType($this->visit($generic->getChild(0)), $this->visit($generic->getChild(1)));
            }

            if ($generic->getChildrenNumber() === 1) {
                return new ListType($this->visit($generic->getChild(0)));
            }
        }

        if ($element->getId() === Nodes::LIST) {
            $depth = ($element->getChildrenNumber() - 1) / 2;
            $list  = new ListType($this->visit($element->getChild(0)));

            while (--$depth > 0) {
                $list = new ListType($list);
            }

            return $list;
        }

        if ($element->getId() === Nodes::UNION) {
            return new UnionType(
                ...array_map(
                    function (TreeNode $node) : Type {
                        return $this->visit($node);
                    },
                    $element->getChildren()
                )
            );
        }

        if ($element->getId() === Nodes::INTERSECTION) {
            return new IntersectionType(
                ...array_map(
                    function (TreeNode $node) : Type {
                        return $this->visit($node);
                    },
                    $element->getChildren()
                )
            );
        }

        assert(false, 'Unsupported node.');
    }
}

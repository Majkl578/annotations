<?php

declare(strict_types=1);

namespace Doctrine\Annotations\TypeParser;

use Doctrine\Annotations\Metadata\Type\BooleanType;
use Doctrine\Annotations\Metadata\Type\FloatType;
use Doctrine\Annotations\Metadata\Type\IntegerType;
use Doctrine\Annotations\Metadata\Type\IntersectionType;
use Doctrine\Annotations\Metadata\Type\ListType;
use Doctrine\Annotations\Metadata\Type\MapType;
use Doctrine\Annotations\Metadata\Type\MixedType;
use Doctrine\Annotations\Metadata\Type\NullType;
use Doctrine\Annotations\Metadata\Type\ObjectType;
use Doctrine\Annotations\Metadata\Type\StringType;
use Doctrine\Annotations\Metadata\Type\Type;
use Doctrine\Annotations\Metadata\Type\UnionType;
use Doctrine\Annotations\Parser\Ast\Reference;
use Doctrine\Annotations\Parser\Reference\ReferenceResolver;
use Doctrine\Annotations\Parser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use function array_map;
use function assert;
use function count;
use function ltrim;
use function strcasecmp;
use function strtolower;

final class PHPStanTypeParser implements TypeParser
{
    /** @var Lexer */
    private $lexer;

    /** @var PhpDocParser */
    private $phpDocParser;

    /** @var ReferenceResolver */
    private $referenceResolver;

    public function __construct(Lexer $lexer, PhpDocParser $phpDocParser, ReferenceResolver $referenceResolver)
    {
        $this->lexer             = $lexer;
        $this->phpDocParser      = $phpDocParser;
        $this->referenceResolver = $referenceResolver;
    }

    public function parsePropertyType(string $docBlock, Scope $scope) : Type
    {
        $tags = $this->parse($docBlock)->getVarTagValues();

        assert(count($tags) <= 1, 'multiple @var tags not allowed');

        if (count($tags) === 0) {
            return new MixedType();
        }

        return $this->resolveType($tags[0]->type, $scope);
    }

    private function parse(string $docBlock) : PhpDocNode
    {
        return $this->phpDocParser->parse(new TokenIterator($this->lexer->tokenize($docBlock)));
    }

    private function resolveType(TypeNode $typeNode, Scope $scope) : Type
    {
        if ($typeNode instanceof IdentifierTypeNode && strcasecmp($typeNode->name, 'null') === 0) {
            return new NullType();
        }

        if ($typeNode instanceof NullableTypeNode) {
            return new UnionType($this->resolveType($typeNode->type, $scope), new NullType());
        }

        if ($typeNode instanceof UnionTypeNode) {
            return new UnionType(
                ...array_map(
                    function (TypeNode $type) use ($scope) : Type {
                        return $this->resolveType($type, $scope);
                    },
                    $typeNode->types
                )
            );
        }

        if ($typeNode instanceof IntersectionTypeNode) {
            return new IntersectionType(
                ...array_map(
                    function (TypeNode $type) use ($scope) : Type {
                        return $this->resolveType($type, $scope);
                    },
                    $typeNode->types
                )
            );
        }

        if ($typeNode instanceof ArrayTypeNode) {
            return $this->resolveArrayTypeNode($typeNode, $scope);
        }

        if ($typeNode instanceof GenericTypeNode) {
            return $this->resolveGenericNode($typeNode, $scope);
        }

        assert($typeNode instanceof IdentifierTypeNode, 'Unsupported node type');

        return $this->resolveIdentifierNode($typeNode, $scope);
    }

    private function resolveGenericNode(GenericTypeNode $typeNode, Scope $scope) : Type
    {
        assert(
            strcasecmp($typeNode->type->name, 'array') === 0
            || strcasecmp($typeNode->type->name, 'iterable') === 0
        );

        if (count($typeNode->genericTypes) === 1) {
            return new ListType($this->resolveType($typeNode->genericTypes[0], $scope));
        }

        if (count($typeNode->genericTypes) === 2) {
            assert($typeNode->genericTypes[0] instanceof IdentifierTypeNode);

            return new MapType(
                $this->resolveIdentifierNode($typeNode->genericTypes[0], $scope),
                $this->resolveType($typeNode->genericTypes[1], $scope)
            );
        }

        assert(false, '>2 generic type args');
    }

    private function resolveArrayTypeNode(ArrayTypeNode $typeNode, Scope $scope) : Type
    {
        return new ListType($this->resolveType($typeNode->type, $scope));
    }

    private function resolveIdentifierNode(TypeNode $typeNode, Scope $scope) : Type
    {
        assert($typeNode instanceof IdentifierTypeNode);

        $canonicalName = strtolower($typeNode->name);

        switch ($canonicalName) {
            case 'bool':
            case 'boolean':
                return new BooleanType();
            case 'int':
            case 'integer':
                return new IntegerType();
            case 'float':
            case 'double':
            case 'real':
                return new FloatType();
            case 'string':
                return new StringType();
            case 'array':
                return new MapType(new UnionType(new IntegerType(), new StringType()), new MixedType());
            case 'mixed':
                return new MixedType(); // TODO not really a scalar
        }

        $fullyQualified = $typeNode->name[0] === '\\';
        return new ObjectType(
            $this->referenceResolver->resolve(
                new Reference(
                    $fullyQualified ? ltrim($typeNode->name, '\\') : $typeNode->name,
                    $fullyQualified
                ),
                $scope
            )
        );
    }
}

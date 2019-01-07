<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Parser;

use Doctrine\Annotations\Parser\Ast\Annotation;
use Doctrine\Annotations\Parser\Ast\Annotations;
use Doctrine\Annotations\Parser\Ast\ClassConstantFetch;
use Doctrine\Annotations\Parser\Ast\Collection\Collection;
use Doctrine\Annotations\Parser\Ast\Collection\NamedEntry;
use Doctrine\Annotations\Parser\Ast\Collection\PositionalEntry;
use Doctrine\Annotations\Parser\Ast\ConstantFetch;
use Doctrine\Annotations\Parser\Ast\Node;
use Doctrine\Annotations\Parser\Ast\Parameter\NamedParameter;
use Doctrine\Annotations\Parser\Ast\Parameter\UnnamedParameter;
use Doctrine\Annotations\Parser\Ast\Parameters;
use Doctrine\Annotations\Parser\Ast\Reference;
use Doctrine\Annotations\Parser\Ast\Scalar\BooleanScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\FloatScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\Identifier;
use Doctrine\Annotations\Parser\Ast\Scalar\IntegerScalar;
use Doctrine\Annotations\Parser\Ast\Scalar\StringScalar;
use Doctrine\Annotations\Parser\Compiler;
use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    /** @var Compiler */
    private $compiler;

    public function setUp() : void
    {
        $this->compiler = new Compiler();
    }

    /**
     * @dataProvider examples
     */
    public function testCompile(string $givenDocblock, Node $esxpectedAst) : void
    {
        $result = $this->compiler->compile($givenDocblock);

        $this->assertEquals($esxpectedAst, $result);
    }

    /**
     * @return mixed[]
     */
    public function examples() : iterable
    {
        yield 'simple with no parenthesis' => [
            <<<'DOCBLOCK'
/**
* @Annotation
*/
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Annotation', false),
                    new Parameters()
                )
            ),
        ];

        yield 'simple with empty parenthesis' => [
            <<<'DOCBLOCK'
/**
* @Annotation()
*/
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Annotation', false),
                    new Parameters()
                )
            ),
        ];

        yield 'multiple without parameters' => [
            <<<'DOCBLOCK'
/** @Annotation1 @Annotation2 @Annotation3 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Annotation1', false),
                    new Parameters()
                ),
                new Annotation(
                    new Reference('Annotation2', false),
                    new Parameters()
                ),
                new Annotation(
                    new Reference('Annotation3', false),
                    new Parameters()
                )
            ),
        ];

        yield 'multiple with comments' => [
            <<<'DOCBLOCK'
/**
 * Hello world
 * @Annotation1
 * Hola mundo
 * @Annotation2
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Annotation1', false),
                    new Parameters()
                ),
                new Annotation(
                    new Reference('Annotation2', false),
                    new Parameters()
                )
            ),
        ];

        yield 'fully qualified with parameter' => [
            <<<'DOCBLOCK'
/**
* @\Ns\Annotation("value")
*/
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Ns\Annotation', true),
                    new Parameters(
                        new UnnamedParameter(new StringScalar('value'))
                    )
                )
            ),
        ];

        yield 'with array' => [
            <<<'DOCBLOCK'
/**
* @return array<string>
*/
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('return', false),
                    new Parameters()
                )
            ),
        ];

        yield 'constants' => [
            <<<'DOCBLOCK'
/**
* @Foo(PHP_EOL, Doctrine, DateTimeInterface::RFC3339, \Doctrine\Annotations\Reader::class)
*/
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Foo', false),
                    new Parameters(
                        new UnnamedParameter(
                            new ConstantFetch(new Identifier('PHP_EOL'))
                        ),
                        new UnnamedParameter(
                            new ConstantFetch(new Identifier('Doctrine'))
                        ),
                        new UnnamedParameter(
                            new ClassConstantFetch(
                                new Reference('DateTimeInterface', false),
                                new Identifier('RFC3339')
                            )
                        ),
                        new UnnamedParameter(
                            new ClassConstantFetch(
                                new Reference('Doctrine\Annotations\Reader', true),
                                new Identifier('class')
                            )
                        )
                    )
                )
            ),
        ];

        yield 'fully qualified, nested, multiple parameters' =>  [
            <<<'DOCBLOCK'
/**
* @\Ns\Name(int=1, annot=@Annot, float=1.2)
*/
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Ns\Name', true),
                    new Parameters(
                        new NamedParameter(
                            new Identifier('int'),
                            new IntegerScalar(1)
                        ),
                        new NamedParameter(
                            new Identifier('annot'),
                            new Annotation(
                                new Reference('Annot', false),
                                new Parameters()
                            )
                        ),
                        new NamedParameter(
                            new Identifier('float'),
                            new FloatScalar(1.2)
                        )
                    )
                )
            ),
        ];

        yield 'nested, with arrays' => [
            <<<'DOCBLOCK'
/**
* @Annot(
*  v1={1,2,3},
*  v2={@one,@two,@three},
*  v3={one=1,two=2,three=3},
*  v4={one=@one(1),two=@two(2),three=@three(3)}
* )
*/
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Annot', false),
                    new Parameters(
                        new NamedParameter(
                            new Identifier('v1'),
                            new Collection(
                                new PositionalEntry(new IntegerScalar(1)),
                                new PositionalEntry(new IntegerScalar(2)),
                                new PositionalEntry(new IntegerScalar(3))
                            )
                        ),
                        new NamedParameter(
                            new Identifier('v2'),
                            new Collection(
                                new PositionalEntry(
                                    new Annotation(
                                        new Reference('one', false),
                                        new Parameters()
                                    )
                                ),
                                new PositionalEntry(
                                    new Annotation(
                                        new Reference('two', false),
                                        new Parameters()
                                    )
                                ),
                                new PositionalEntry(
                                    new Annotation(
                                        new Reference('three', false),
                                        new Parameters()
                                    )
                                )
                            )
                        ),
                        new NamedParameter(
                            new Identifier('v3'),
                            new Collection(
                                new NamedEntry(new Identifier('one'), new IntegerScalar(1)),
                                new NamedEntry(new Identifier('two'), new IntegerScalar(2)),
                                new NamedEntry(new Identifier('three'), new IntegerScalar(3))
                            )
                        ),
                        new NamedParameter(
                            new Identifier('v4'),
                            new Collection(
                                new NamedEntry(
                                    new Identifier('one'),
                                    new Annotation(
                                        new Reference('one', false),
                                        new Parameters(
                                            new UnnamedParameter(new IntegerScalar(1))
                                        )
                                    )
                                ),
                                new NamedEntry(
                                    new Identifier('two'),
                                    new Annotation(
                                        new Reference('two', false),
                                        new Parameters(
                                            new UnnamedParameter(new IntegerScalar(2))
                                        )
                                    )
                                ),
                                new NamedEntry(
                                    new Identifier('three'),
                                    new Annotation(
                                        new Reference('three', false),
                                        new Parameters(
                                            new UnnamedParameter(new IntegerScalar(3))
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            ),
        ];

        yield 'ORM Id example' => [
            <<<'DOCBLOCK'
/**
 * @ORM\Id @ORM\Column(type="integer")
 * @ORM\GeneratedValue
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('ORM\\Id', false),
                    new Parameters()
                ),
                new Annotation(
                    new Reference('ORM\\Column', false),
                    new Parameters(
                        new NamedParameter(
                            new Identifier('type'),
                            new StringScalar('integer')
                        )
                    )
                ),
                new Annotation(
                    new Reference('ORM\\GeneratedValue', false),
                    new Parameters()
                )
            ),
        ];

        yield 'unicode' => [
            <<<'DOCBLOCK'
/**
 * @Fancy😊Annotation
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Fancy😊Annotation', false),
                    new Parameters()
                )
            ),
        ];

        yield 'spaces after @' => [
            <<<'DOCBLOCK'
/**
 * @
 * @ Hello world
 */
DOCBLOCK
            ,
            new Annotations(),
        ];

        yield 'numbers' => [
            <<<'DOCBLOCK'
/**
 * @Annotation(1, 123, -123, 1.2, 123.456, -123.456, 1e2, 123e456, 1.2e-3, -123.456E-789)
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Annotation', false),
                    new Parameters(
                        new UnnamedParameter(new IntegerScalar(1)),
                        new UnnamedParameter(new IntegerScalar(123)),
                        new UnnamedParameter(new IntegerScalar(-123)),
                        new UnnamedParameter(new FloatScalar(1.2)),
                        new UnnamedParameter(new FloatScalar(123.456)),
                        new UnnamedParameter(new FloatScalar(-123.456)),
                        new UnnamedParameter(new FloatScalar(1e2)),
                        new UnnamedParameter(new FloatScalar(123e456)),
                        new UnnamedParameter(new FloatScalar(1.2e-3)),
                        new UnnamedParameter(new FloatScalar(-123.456E-789))
                    )
                )
            ),
        ];

        yield 'ORM Column example' => [
            <<<'DOCBLOCK'
/** @ORM\Column(type="string", length=50, nullable=true) */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('ORM\\Column', false),
                    new Parameters(
                        new NamedParameter(
                            new Identifier('type'),
                            new StringScalar('string')
                        ),
                        new NamedParameter(
                            new Identifier('length'),
                            new IntegerScalar(50)
                        ),
                        new NamedParameter(
                            new Identifier('nullable'),
                            new BooleanScalar(true)
                        )
                    )
                )
            ),
        ];

        yield 'complex ORM M:N' => [
            <<<'DOCBLOCK'
/**
 * @ORM\ManyToMany(targetEntity=CmsGroup::class, inversedBy="users", cascade={"persist"})
 * @ORM\JoinTable(name="cms_users_groups",
 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
 *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
 * )
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('ORM\\ManyToMany', false),
                    new Parameters(
                        new NamedParameter(
                            new Identifier('targetEntity'),
                            new ClassConstantFetch(
                                new Reference('CmsGroup', false),
                                new Identifier('class')
                            )
                        ),
                        new NamedParameter(
                            new Identifier('inversedBy'),
                            new StringScalar('users')
                        ),
                        new NamedParameter(
                            new Identifier('cascade'),
                            new Collection(
                                new PositionalEntry(
                                    new StringScalar('persist')
                                )
                            )
                        )
                    )
                ),
                new Annotation(
                    new Reference('ORM\\JoinTable', false),
                    new Parameters(
                        new NamedParameter(
                            new Identifier('name'),
                            new StringScalar('cms_users_groups')
                        ),
                        new NamedParameter(
                            new Identifier('joinColumns'),
                            new Collection(
                                new PositionalEntry(
                                    new Annotation(
                                        new Reference('ORM\JoinColumn', false),
                                        new Parameters(
                                            new NamedParameter(
                                                new Identifier('name'),
                                                new StringScalar('user_id')
                                            ),
                                            new NamedParameter(
                                                new Identifier('referencedColumnName'),
                                                new StringScalar('id')
                                            )
                                        )
                                    )
                                )
                            )
                        ),
                        new NamedParameter(
                            new Identifier('inverseJoinColumns'),
                            new Collection(
                                new PositionalEntry(
                                    new Annotation(
                                        new Reference('ORM\JoinColumn', false),
                                        new Parameters(
                                            new NamedParameter(
                                                new Identifier('name'),
                                                new StringScalar('group_id')
                                            ),
                                            new NamedParameter(
                                                new Identifier('referencedColumnName'),
                                                new StringScalar('id')
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            ),
        ];

        yield 'Symfony route' => [
            <<<'DOCBLOCK'
/**
 * @Route("/argument_with_route_param_and_default/{value}", defaults={"value": "value"}, name="argument_with_route_param_and_default")
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Route', false),
                    new Parameters(
                        new UnnamedParameter(new StringScalar('/argument_with_route_param_and_default/{value}')),
                        new NamedParameter(
                            new Identifier('defaults'),
                            new Collection(
                                new NamedEntry(new StringScalar('value'), new StringScalar('value'))
                            )
                        ),
                        new NamedParameter(
                            new Identifier('name'),
                            new StringScalar('argument_with_route_param_and_default')
                        )
                    )
                )
            ),
        ];

        yield 'SymfonyFrameworkExtraBundle annotations' => [
            <<<'DOCBLOCK'
/**
 * @Route("/is_granted/resolved/conflict")
 * @IsGranted("ISGRANTED_VOTER", subject="request")
 * @Security("is_granted('ISGRANTED_VOTER', request)")
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Route', false),
                    new Parameters(
                        new UnnamedParameter(new StringScalar('/is_granted/resolved/conflict'))
                    )
                ),
                new Annotation(
                    new Reference('IsGranted', false),
                    new Parameters(
                        new UnnamedParameter(new StringScalar('ISGRANTED_VOTER')),
                        new NamedParameter(new Identifier('subject'), new StringScalar('request'))
                    )
                ),
                new Annotation(
                    new Reference('Security', false),
                    new Parameters(
                        new UnnamedParameter(new StringScalar('is_granted(\'ISGRANTED_VOTER\', request)'))
                    )
                )
            ),
        ];

        yield 'JMS Serializer field' => [
            <<<'DOCBLOCK'
/**
 * @Type("array<string,string>")
 * @SerializedName("addresses")
 * @XmlElement(namespace="http://example.com/namespace2")
 * @XmlMap(inline = false, entry = "address", keyAttribute = "id", namespace="http://example.com/namespace2")
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Type', false),
                    new Parameters(
                        new UnnamedParameter(new StringScalar('array<string,string>'))
                    )
                ),
                new Annotation(
                    new Reference('SerializedName', false),
                    new Parameters(
                        new UnnamedParameter(new StringScalar('addresses'))
                    )
                ),
                new Annotation(
                    new Reference('XmlElement', false),
                    new Parameters(
                        new NamedParameter(new Identifier('namespace'), new StringScalar('http://example.com/namespace2'))
                    )
                ),
                new Annotation(
                    new Reference('XmlMap', false),
                    new Parameters(
                        new NamedParameter(
                            new Identifier('inline'),
                            new BooleanScalar(false)
                        ),
                        new NamedParameter(
                            new Identifier('entry'),
                            new StringScalar('address')
                        ),
                        new NamedParameter(
                            new Identifier('keyAttribute'),
                            new StringScalar('id')
                        ),
                        new NamedParameter(
                            new Identifier('namespace'),
                            new StringScalar('http://example.com/namespace2')
                        )
                    )
                )
            ),
        ];

        yield 'string escaping' => [
            <<<'DOCBLOCK'
/**
 * @Annotation("", "foo", "b\"a\"r", "ba\\z", "bla\h", "\\\\hello\\\\")
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Annotation', false),
                    new Parameters(
                        new UnnamedParameter(new StringScalar('')),
                        new UnnamedParameter(new StringScalar('foo')),
                        new UnnamedParameter(new StringScalar('b\\"a\\"r')),
                        new UnnamedParameter(new StringScalar('ba\\z')),
                        new UnnamedParameter(new StringScalar('bla\\h')),
                        new UnnamedParameter(new StringScalar('\\\\hello\\\\'))
                    )
                )
            ),
        ];

        yield 'constants' => [
            <<<'DOCBLOCK'
/**
 * @Annotation(Foo\Bar::BAZ, \Foo\Bar\Baz::BLAH)
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Annotation', false),
                    new Parameters(
                        new UnnamedParameter(
                            new ClassConstantFetch(
                                new Reference('Foo\Bar', false),
                                new Identifier('BAZ')
                            )
                        ),
                        new UnnamedParameter(
                            new ClassConstantFetch(
                                new Reference('Foo\Bar\Baz', true),
                                new Identifier('BLAH')
                            )
                        )
                    )
                )
            ),
        ];

        yield [
            <<<'DOCBLOCK'
/**
 * @TrailingComma(
 *     123,
 *     @Foo(1, 2, 3,),
 *     @Bar,
 * )
 */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('TrailingComma', false),
                    new Parameters(
                        new UnnamedParameter(new IntegerScalar(123)),
                        new UnnamedParameter(
                            new Annotation(
                                new Reference('Foo', false),
                                new Parameters(
                                    new UnnamedParameter(new IntegerScalar(1)),
                                    new UnnamedParameter(new IntegerScalar(2)),
                                    new UnnamedParameter(new IntegerScalar(3))
                                )
                            )
                        ),
                        new UnnamedParameter(
                            new Annotation(
                                new Reference('Bar', false),
                                new Parameters()
                            )
                        )
                    )
                )
            ),
        ];

        yield [
            <<<'DOCBLOCK'
/** @Foo({123, 456 = 789, 0}) */
DOCBLOCK
            ,
            new Annotations(
                new Annotation(
                    new Reference('Foo', false),
                    new Parameters(
                        new UnnamedParameter(
                            new Collection(
                                new PositionalEntry(
                                    new IntegerScalar(123)
                                ),
                                new NamedEntry(
                                    new IntegerScalar(456),
                                    new IntegerScalar(789)
                                ),
                                new PositionalEntry(
                                    new IntegerScalar(0)
                                )
                            )
                        )
                    )
                )
            ),
        ];
    }
}

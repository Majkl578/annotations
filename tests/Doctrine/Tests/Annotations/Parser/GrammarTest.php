<?php

declare(strict_types=1);

namespace Doctrine\Tests\Annotations\Parser;

use Hoa\Compiler\Exception\UnrecognizedToken;
use Hoa\Compiler\Llk\Llk;
use Hoa\Compiler\Llk\Parser as LlkParser;
use Hoa\Compiler\Visitor\Dump;
use Hoa\File\Read;
use PHPUnit\Framework\TestCase;

final class GrammarTest extends TestCase
{
    /** @var LlkParser */
    private $compiler;

    protected function setUp() : void
    {
        $this->compiler = Llk::load(new Read(__DIR__ . '/../../../../../lib/Doctrine/Annotations/Parser/grammar.pp'));
    }

    /**
     * @dataProvider validDocBlocksProvider()
     * @dataProvider validParityDocBlocksProvider()
     */
    public function testValidGrammar(string $docBlock, string $expectedTrace) : void
    {
        $ast   = $this->compiler->parse($docBlock);
        $trace = (new Dump())->visit($ast);

        self::assertSame($expectedTrace, $trace);
    }

    /**
     * @dataProvider invalidGrammarProvider()
     */
    public function testInvalidGrammar(string $docBlock, string $expectedError) : void
    {
        $this->expectException(UnrecognizedToken::class);
        $this->expectExceptionMessage($expectedError);

        $this->compiler->parse($docBlock);
    }

    /**
     * @return string[][]
     */
    public function validDocBlocksProvider() : iterable
    {
        yield 'simple with no parenthesis' => [
            <<<'DOCBLOCK'
/**
* @Annotation
*/
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, Annotation)

TRACE
            ,
        ];

        yield 'simple with empty parenthesis' => [
            <<<'DOCBLOCK'
/**
* @Annotation()
*/
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Annotation)

TRACE
            ,
        ];

        yield 'multiple without parameters' => [
            <<<'DOCBLOCK'
/** @Annotation1 @Annotation2 @Annotation3 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, Annotation1)
>  >  #annotation
>  >  >  token(annot:simple_identifier, Annotation2)
>  >  #annotation
>  >  >  token(annot:simple_identifier, Annotation3)

TRACE
            ,
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
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, Annotation1)
>  >  #annotation
>  >  >  token(annot:simple_identifier, Annotation2)

TRACE
            ,
        ];

        yield 'fully qualified with parameter' => [
            <<<'DOCBLOCK'
/**
* @\Ns\Annotation("value")
*/
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, \Ns\Annotation)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, value)

TRACE
            ,
        ];

        yield 'with array' => [
            <<<'DOCBLOCK'
/**
* @return array<string>
*/
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, return)

TRACE
            ,
        ];

        yield 'fully qualified, nested, multiple parameters' => [
            <<<'DOCBLOCK'
/**
* @\Ns\Name(int=1, annot=@Annot, float=1.2)
*/
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, \Ns\Name)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, int)
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:integer, 1)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, annot)
>  >  >  >  >  #value
>  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  token(annot:simple_identifier, Annot)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, float)
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:float, 1.2)

TRACE
            ,
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
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Annot)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, v1)
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 1)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 2)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 3)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, v2)
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  token(annot:simple_identifier, one)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  token(annot:simple_identifier, two)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  token(annot:simple_identifier, three)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, v3)
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  token(value:identifier, one)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 1)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  token(value:identifier, two)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 2)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  token(value:identifier, three)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 3)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, v4)
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  token(value:identifier, one)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  token(annot:valued_identifier, one)
>  >  >  >  >  >  >  >  >  >  #parameters
>  >  >  >  >  >  >  >  >  >  >  #unnamed_parameter
>  >  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  >  token(value:integer, 1)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  token(value:identifier, two)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  token(annot:valued_identifier, two)
>  >  >  >  >  >  >  >  >  >  #parameters
>  >  >  >  >  >  >  >  >  >  >  #unnamed_parameter
>  >  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  >  token(value:integer, 2)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  token(value:identifier, three)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  token(annot:valued_identifier, three)
>  >  >  >  >  >  >  >  >  >  #parameters
>  >  >  >  >  >  >  >  >  >  >  #unnamed_parameter
>  >  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  >  token(value:integer, 3)

TRACE
            ,
        ];

        yield 'ORM Id example' => [
            <<<'DOCBLOCK'
/**
 * @ORM\Id @ORM\Column(type="integer")
 * @ORM\GeneratedValue
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, ORM\Id)
>  >  #annotation
>  >  >  token(annot:valued_identifier, ORM\Column)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, type)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, integer)
>  >  #annotation
>  >  >  token(annot:simple_identifier, ORM\GeneratedValue)

TRACE
            ,
        ];

        yield 'unicode' => [
            <<<'DOCBLOCK'
/**
 * @Fancy😊Annotation
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, Fancy😊Annotation)

TRACE
            ,
        ];

        yield 'spaces after @' => [
            <<<'DOCBLOCK'
/**
 * @
 * @ Hello world
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations

TRACE
            ,
        ];

        yield 'numbers' => [
            <<<'DOCBLOCK'
/**
 * @Annotation(1, 123, -123, 1.2, 123.456, -123.456, 1e2, 123e456, 1.2e-3, -123.456E-789)
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Annotation)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:integer, 1)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:integer, 123)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:integer, -123)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:float, 1.2)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:float, 123.456)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:float, -123.456)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:float, 1e2)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:float, 123e456)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:float, 1.2e-3)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:float, -123.456E-789)

TRACE
            ,
        ];

        yield 'ORM Column example' => [
            <<<'DOCBLOCK'
/** @ORM\Column(type="string", length=50, nullable=true) */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, ORM\Column)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, type)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, string)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, length)
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:integer, 50)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, nullable)
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:boolean, true)

TRACE
            ,
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
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, ORM\ManyToMany)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, targetEntity)
>  >  >  >  >  #value
>  >  >  >  >  >  #class_constant
>  >  >  >  >  >  >  #reference
>  >  >  >  >  >  >  >  token(value:identifier, CmsGroup)
>  >  >  >  >  >  >  token(value:identifier, class)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, inversedBy)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, users)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, cascade)
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  token(string:string, persist)
>  >  #annotation
>  >  >  token(annot:valued_identifier, ORM\JoinTable)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, name)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, cms_users_groups)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, joinColumns)
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  token(annot:valued_identifier, ORM\JoinColumn)
>  >  >  >  >  >  >  >  >  >  #parameters
>  >  >  >  >  >  >  >  >  >  >  #named_parameter
>  >  >  >  >  >  >  >  >  >  >  >  token(value:identifier, name)
>  >  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  >  >  >  >  token(string:string, user_id)
>  >  >  >  >  >  >  >  >  >  >  #named_parameter
>  >  >  >  >  >  >  >  >  >  >  >  token(value:identifier, referencedColumnName)
>  >  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  >  >  >  >  token(string:string, id)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, inverseJoinColumns)
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  token(annot:valued_identifier, ORM\JoinColumn)
>  >  >  >  >  >  >  >  >  >  #parameters
>  >  >  >  >  >  >  >  >  >  >  #named_parameter
>  >  >  >  >  >  >  >  >  >  >  >  token(value:identifier, name)
>  >  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  >  >  >  >  token(string:string, group_id)
>  >  >  >  >  >  >  >  >  >  >  #named_parameter
>  >  >  >  >  >  >  >  >  >  >  >  token(value:identifier, referencedColumnName)
>  >  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  >  >  >  >  token(string:string, id)

TRACE
            ,
        ];

        yield 'Symfony route' => [
            <<<'DOCBLOCK'
/**
 * @Route("/argument_with_route_param_and_default/{value}", defaults={"value": "value"}, name="argument_with_route_param_and_default")
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Route)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, /argument_with_route_param_and_default/{value})
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, defaults)
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  token(string:string, value)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  token(string:string, value)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, name)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, argument_with_route_param_and_default)

TRACE
            ,
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
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Route)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, /is_granted/resolved/conflict)
>  >  #annotation
>  >  >  token(annot:valued_identifier, IsGranted)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, ISGRANTED_VOTER)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, subject)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, request)
>  >  #annotation
>  >  >  token(annot:valued_identifier, Security)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, is_granted('ISGRANTED_VOTER', request))

TRACE
            ,
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
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Type)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, array<string,string>)
>  >  #annotation
>  >  >  token(annot:valued_identifier, SerializedName)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, addresses)
>  >  #annotation
>  >  >  token(annot:valued_identifier, XmlElement)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, namespace)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, http://example.com/namespace2)
>  >  #annotation
>  >  >  token(annot:valued_identifier, XmlMap)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, inline)
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:boolean, false)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, entry)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, address)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, keyAttribute)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, id)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, namespace)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, http://example.com/namespace2)

TRACE
            ,
        ];

        yield 'string escaping' => [
            <<<'DOCBLOCK'
/**
 * @Annotation("", "foo", "b\"a\"r", "ba\\z", "bla\h", "\\\\hello\\\\")
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Annotation)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, foo)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, b\"a\"r)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, ba\\z)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, bla\h)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, \\\\hello\\\\)

TRACE
            ,
        ];

        yield 'constants' => [
            <<<'DOCBLOCK'
/**
 * @Annotation(Foo\Bar::BAZ, \Foo\Bar\Baz::BLAH, PHP_EOL)
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Annotation)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #class_constant
>  >  >  >  >  >  >  #reference
>  >  >  >  >  >  >  >  token(value:identifier_ns, Foo\Bar)
>  >  >  >  >  >  >  token(value:identifier, BAZ)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #class_constant
>  >  >  >  >  >  >  #reference
>  >  >  >  >  >  >  >  token(value:identifier_ns, \Foo\Bar\Baz)
>  >  >  >  >  >  >  token(value:identifier, BLAH)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #standalone_constant
>  >  >  >  >  >  >  token(value:identifier, PHP_EOL)

TRACE
            ,
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
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, TrailingComma)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  token(value:integer, 123)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  token(annot:valued_identifier, Foo)
>  >  >  >  >  >  >  #parameters
>  >  >  >  >  >  >  >  #unnamed_parameter
>  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  token(value:integer, 1)
>  >  >  >  >  >  >  >  #unnamed_parameter
>  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  token(value:integer, 2)
>  >  >  >  >  >  >  >  #unnamed_parameter
>  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  token(value:integer, 3)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  token(annot:simple_identifier, Bar)

TRACE
            ,
        ];

        yield 'inline annotation' => [
            <<<'DOCBLOCK'
/**
 * Hello world from @Annotation
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, Annotation)

TRACE
            ,
        ];

        yield 'oneline annotation' => [
            <<<'DOCBLOCK'
/** @var string */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, var)

TRACE
            ,
        ];

        yield 'mixed collections' => [
            <<<'DOCBLOCK'
/** @Foo({123, 456 = 789, 0}) */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Foo)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 123)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  token(value:integer, 456)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 789)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 0)

TRACE
            ,
        ];
    }

    /**
     * @return string[][]
     */
    public function validParityDocBlocksProvider() : iterable
    {
        /** @see DocParserTest::testNestedArraysWithNestedAnnotation() */
        yield 'Nested arrays with nested annotations' => [
            <<<'DOCBLOCK'
/**
 * @Name(foo={1,2, {"key"=@Name}})
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Name)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, foo)
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 1)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  token(value:integer, 2)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #collection
>  >  >  >  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  >  >  token(string:string, key)
>  >  >  >  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  >  >  >  >  >  >  token(annot:simple_identifier, Name)

TRACE
        ];

        /** @see DocParserTest::testBasicAnnotations() */
        yield 'Basic annotations: Marker annotation' => [
            <<<'DOCBLOCK'
/**
 * @Name
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, Name)

TRACE
        ];

        /** @see DocParserTest::testBasicAnnotations() */
        yield 'Basic annotations: Associative arrays' => [
            <<<'DOCBLOCK'
/**
 * @Name(foo={"key1" = "value1"})
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Name)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, foo)
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  token(string:string, key1)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  token(string:string, value1)

TRACE
        ];

        /** @see DocParserTest::testBasicAnnotations() */
        yield 'Basic annotations: Numerical arrays' => [
            <<<'DOCBLOCK'
/**
 * @Name({2="foo", 4="bar"})
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Name)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  token(value:integer, 2)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  token(string:string, foo)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  token(value:integer, 4)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  token(string:string, bar)

TRACE
        ];

        /** @see DocParserTest::testBasicAnnotations() */
        yield 'Basic annotations: Multiple values' => [
            <<<'DOCBLOCK'
/**
 * @Name(@Name, @Name)
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Name)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  token(annot:simple_identifier, Name)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  token(annot:simple_identifier, Name)

TRACE
        ];

        /** @see DocParserTest::testBasicAnnotations() */
        yield 'Basic annotations: Multiple types as values' => [
            <<<'DOCBLOCK'
/**
 * @Name(foo="Bar", @Name, {"key1"="value1", "key2"="value2"})
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Name)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, foo)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, Bar)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #annotation
>  >  >  >  >  >  >  token(annot:simple_identifier, Name)
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  token(string:string, key1)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  token(string:string, value1)
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  token(string:string, key2)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  token(string:string, value2)

TRACE
        ];

        /** @see DocParserTest::testBasicAnnotations() */
        yield 'Basic annotations: Complete docblock' => [
            <<<'DOCBLOCK'
/**
 * Some nifty class.
 *
 * @author Mr.X
 * @Name(foo="bar")
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, author)
>  >  #annotation
>  >  >  token(annot:valued_identifier, Name)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, foo)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, bar)

TRACE
        ];

        /** @see DocParserTest::testDefaultValueAnnotations() */
        yield 'Default value annotations: Array as first value' => [
            <<<'DOCBLOCK'
/**
 * @Name({"key1"="value1"})
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Name)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  token(string:string, key1)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  token(string:string, value1)

TRACE
        ];

        /** @see DocParserTest::testDefaultValueAnnotations() */
        yield 'Default value annotations: Array as first value and additional values' => [
            <<<'DOCBLOCK'
/**
 * @Name({"key1"="value1"}, foo="bar")
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:valued_identifier, Name)
>  >  >  #parameters
>  >  >  >  #unnamed_parameter
>  >  >  >  >  #value
>  >  >  >  >  >  #collection
>  >  >  >  >  >  >  #collection_entry
>  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  token(string:string, key1)
>  >  >  >  >  >  >  >  #value
>  >  >  >  >  >  >  >  >  #string
>  >  >  >  >  >  >  >  >  >  token(string:string, value1)
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, foo)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, bar)

TRACE
        ];

        /** @see DocParserTest::testDefaultValueAnnotations() */
        yield 'Namespaced annotations' => [
            <<<'DOCBLOCK'
/**
 * Some nifty class.
 *
 * @package foo
 * @subpackage bar
 * @author Mr.X <mr@x.com>
 * @Doctrine\Tests\Annotations\Name(foo="bar")
 * @ignore
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, package)
>  >  #annotation
>  >  >  token(annot:simple_identifier, subpackage)
>  >  #annotation
>  >  >  token(annot:simple_identifier, author)
>  >  #annotation
>  >  >  token(annot:simple_identifier, x)
>  >  #annotation
>  >  >  token(annot:valued_identifier, Doctrine\Tests\Annotations\Name)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, foo)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, bar)
>  >  #annotation
>  >  >  token(annot:simple_identifier, ignore)

TRACE
        ];

        /** @see DocParserTest::testTypicalMethodDocBlock() */
        yield 'Namespaced annotations' => [
            <<<'DOCBLOCK'
/**
 * Some nifty method.
 *
 * @since 2.0
 * @Doctrine\Tests\Annotations\Name(foo="bar")
 * @param string \$foo This is foo.
 * @param mixed \$bar This is bar.
 * @return string Foo and bar.
 * @This is irrelevant
 * @Marker
 */
DOCBLOCK
            ,
            <<<'TRACE'
>  #annotations
>  >  #annotation
>  >  >  token(annot:simple_identifier, since)
>  >  #annotation
>  >  >  token(annot:valued_identifier, Doctrine\Tests\Annotations\Name)
>  >  >  #parameters
>  >  >  >  #named_parameter
>  >  >  >  >  token(value:identifier, foo)
>  >  >  >  >  #value
>  >  >  >  >  >  #string
>  >  >  >  >  >  >  token(string:string, bar)
>  >  #annotation
>  >  >  token(annot:simple_identifier, param)
>  >  #annotation
>  >  >  token(annot:simple_identifier, param)
>  >  #annotation
>  >  >  token(annot:simple_identifier, return)
>  >  #annotation
>  >  >  token(annot:simple_identifier, This)
>  >  #annotation
>  >  >  token(annot:simple_identifier, Marker)

TRACE
        ];
    }

    /**
     * @return string[][]
     */
    public function invalidGrammarProvider() : iterable
    {
        yield 'broken unpaired parenthesis' => [
            <<<'DOCBLOCK'
/** @Foo( */
DOCBLOCK
            ,
            <<<'ERROR'
/** @Foo( */
          ↑
ERROR
        ];

        /** @see DocParserTest::testAnnotationDontAcceptSingleQuotes() */
        yield 'single quotes' => [
            <<<'DOCBLOCK'
/** @Name(foo='bar') */
DOCBLOCK
            ,
            <<<'ERROR'
/** @Name(foo='bar') */
              ↑
ERROR
        ];
    }
}

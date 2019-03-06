%skip whitespace \s+

%token null           (?i)null
%token boolean        (?i)bool(?:ean)?
%token true           (?i)true
%token false          (?i)false
%token string         (?i)string
%token integer        (?i)int(?:eger)?
%token float          (?i)float
%token array          (?i)array
%token object         (?i)object
%token iterable       (?i)iterable
%token callable       (?i)callable

%token comma          ,
%token bracket_       <
%token _bracket       >
%token parenthesis_   \(
%token _parenthesis   \)
%token square_        \[
%token _square        \]
%token union          \|
%token intersection   &

%token identifier     \\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*

type:
    nonList() | list() | intersection() | union()

nonList:
    null()
    | boolean()
    | string()
    | integer()
    | float()
    | array()
    | object()
    | iterable()
    | callable()

#list:
    nonList() ( <square_> <_square> )+

nonUnion:
    null()
    | boolean()
    | string()
    | integer()
    | float()
    | array()
    | object()
    | iterable()
    | callable()
    | list()
    | ( ::parenthesis_:: intersection() ::_parenthesis:: )

nonIntersection:
    null()
    | boolean()
    | string()
    | integer()
    | float()
    | array()
    | object()
    | iterable()
    | callable()
    | list()
    | ( ::parenthesis_:: union() ::_parenthesis:: )

#null:
    ::null::

#boolean:
    ::boolean:: | <true> | <false>

#string:
    ::string::

#integer:
    ::integer::

#float:
    ::float::

#object:
    ::object::
    | ( <identifier> generic()? )

#iterable:
    ::iterable:: generic()?

#callable:
    ::callable::


#array:
    ( ::array:: generic()? )

#generic:
    ::bracket_:: type() ( ::comma:: type() )* ::_bracket::

#union:
    nonUnion() ::union:: nonUnion()

#intersection:
    nonIntersection() ::intersection:: nonIntersection()


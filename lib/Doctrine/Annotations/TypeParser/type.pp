%skip whitespace \s+

%token boolean        (?i)bool
%token integer        (?i)int
%token float          (?i)float
%token string         (?i)string
%token array          (?i)array
%token object         (?i)object

%token null           (?i)null
%token true           (?i)true
%token false          (?i)false

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

#type:
    simple() composition()?

simple:
    null()
    | boolean()
    | integer()
    | float()
    | string()
    | object()
    | class()
    | array()

composition:
    union()+ | intersection()+

#null:
    ::null::

#boolean:
    ::boolean::

#integer:
    ::integer::

#float:
    ::float::

#string:
    ::string::

#object:
    ::object::

#class:
    <identifier>

#array:
    ( ::array:: ::bracket_:: ( ( integer() | string() ) ::comma:: ) type() ::_bracket:: )
    | ( type() ::square_:: ::_square:: )

#union:
    ::union:: ( simple() | ( ::parenthesis_:: type() ) ::_parenthesis:: ) )

#intersection:
    ::intersection:: ( simple() | ( ::parenthesis_:: type() ::_parenthesis:: ) )

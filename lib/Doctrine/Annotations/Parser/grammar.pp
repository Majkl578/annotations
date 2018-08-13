%pragma lexer.unicode 1

%skip   space               [\x20\x09\x0a\x0d]+
%skip   doc_                [/**]
%skip   _doc                [*/]
%skip   star                [*]

%token  at                  @(?!\s)                     -> annot
%token  text                .*

%token  annot:identifier    [\\]?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)* -> values

%skip   values:star         [*]
%skip   values:_doc         [*/]
%skip   values:space        [\x20\x09\x0a\x0d]+
%token  values:comma        ,                           -> value
%token  values:at           @                           -> annot
%token  values:brace_       {                           -> value
%token  values:_brace       }                           -> value
%token  values:parenthesis_ \(                          -> value
%token  values:_parenthesis \)                          -> default
%token  values:text         [^@].*                      -> default

%skip   value:star          [*]
%skip   value:_doc          [*/]
%skip   value:space         [\x20\x09\x0a\x0d]+
%token  value:_parenthesis  \)                          -> values
%token  value:at            @                           -> annot
%token  value:null          null
%token  value:boolean       false|true
%token  value:identifier    [\\]?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*
%token  value:brace_        {
%token  value:_brace        }
%token  value:colon         :
%token  value:comma         ,
%token  value:equals        =
%token  value:number        \-?(0|[1-9]\d*)(\.\d+)?([eE][\+\-]?\d+)?

%token  value:string        "(.*?)(?<!\\)"

#dockblock:
    (comments() | annotations())*

#annotations:
    annotation()+

#annotation:
    ::at:: <identifier> ( parameters() | comments() )?

#comments:
    <text>+

#values:
    value() ( ::comma:: value() )* ::comma::?

#list:
    ::brace_:: ( (value() ( ::comma:: value() )*) ::comma::? )? ::_brace::

#map:
    ::brace_:: pairs() ::comma::? ::_brace::

#pairs:
    pair() ( ::comma:: pair() )*

#pair:
    (<identifier> | <string> | <number>| constant()) ( ::equals:: | ::colon:: ) value()

#value:
    <null> | <boolean> | <string> | <number> | pair() | map() | list() | annotation() | constant()

parameters:
    ( ::parenthesis_:: ( values() )? ::_parenthesis:: ) | <string>?

#constant:
    <identifier> (<colon> <colon> <identifier>)?

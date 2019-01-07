%pragma lexer.unicode 1

%skip   space              [\x20\x09\x0a\x0d]+
%token  doc_               /\*\*                      -> docblock

%skip   docblock:space     [\x20\x09\x0a\x0d]+
%skip   docblock:star      \*(?!/)
%token  docblock:_doc      \*/                         -> default
%token  docblock:at        @(?!\s)                     -> annot
%token  docblock:text      [^*@]+|@(?=\s)|\*(?!/)

%token  annot:valued_identifier \\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*(?=\()
%token  annot:simple_identifier \\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)* -> __shift__
%token  annot:parenthesis_  \(                          -> value

%skip   value:star          [*](?!/)
%skip   value:space         [\x20\x09\x0a\x0d]+
%token  value:_parenthesis  \)                          -> __shift__ * 2
%token  value:at            @(?!\s)                     -> annot
%token  value:comma         ,
%token  value:brace_        {
%token  value:_brace        }
%token  value:double_colon  ::
%token  value:colon         :
%token  value:equals        =
%token  value:quote_        "                           -> string
%token  value:null          \bnull\b
%token  value:boolean       \b(?:true|false)\b
%token  value:float         -?(0|[1-9]\d*)(?=[eE\.])(\.\d+)?([eE][+-]?\d+)?
%token  value:integer       -?(0|[1-9]\d*)
%token  value:identifier_ns \\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+
%token  value:identifier    [a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*

%token  string:string      (?:[^"\\]+|(\\\\)*\\"|(\\\\)+|\\?[^"\\]+)+
%token  string:_quote      "                           -> __shift__

#annotations:
    ::doc_::
    (::text:: | annotation())*
    ::_doc::

#annotation:
    ::at::
    (
        <simple_identifier>
        | ( <valued_identifier> ::parenthesis_:: ( parameters() )? ::_parenthesis:: )
    )

#collection:
    ::brace_:: ( collection_entry() ( ::comma:: collection_entry() )* )? ::comma::? ::_brace::

#collection_entry:
    ( collection_key() ( ::colon:: | ::equals:: ) )? value()

collection_key:
    <integer> | string() | <identifier> | constant()

pairs:
    pair() ( ::comma:: pair() )*

#pair:
    (<identifier> | <identifier_ns> | string() | <integer> | <float> | constant()) ( ::equals:: | ::colon:: ) value()

#value:
    <null> | <boolean> | string() | <integer> | <float> | collection() | annotation() | constant()

#parameters:
    ( parameter() ( ::comma:: parameter())* ::comma::? )?

parameter:
    named_parameter() | unnamed_parameter()

#named_parameter:
    <identifier> ::equals:: value()

#unnamed_parameter:
    value()

constant:
    class_constant() | standalone_constant()

#standalone_constant:
    <identifier>

#class_constant:
    reference() ::double_colon:: <identifier>

#string:
    ::quote_:: <string>? ::_quote::

#reference:
    <identifier> | <identifier_ns>

<?php

// Minimal stubs to make PhpParser nodes AST-aware for PHPStan in this repo.

namespace PhpParser;

class Node
{
    /** @var \PhpParser\Node\Identifier|\PhpParser\Node\Name|string|null */
    public $name;
    /** @var list<\PhpParser\Node\Param> */
    public $params;
    /** @var \PhpParser\Node|null */
    public $returnType;
    /** @var list<\PhpParser\Node\Stmt>|null */
    public $stmts;
    /** @var \PhpParser\Node|list<\PhpParser\Node>|null */
    public $cond;
    /** @var \PhpParser\Node|null */
    public $type;
    /** @var list<\PhpParser\Node> */
    public $types;
    /** @var list<\PhpParser\Node\Const_> */
    public $consts;
    /** @var \PhpParser\Node|null */
    public $default;
    /** @var \PhpParser\Node|null */
    public $expr;
    /** @var \PhpParser\Node|null */
    public $var;
    /** @var list<\PhpParser\Node> */
    public $args;
    /** @var list<\PhpParser\Node> */
    public $vars;
    /** @var bool */
    public $variadic;
    /** @var int */
    public $flags;
    /** @var bool */
    public $byRef;
    /** @var mixed */
    public $value;
    /** @var \PhpParser\Node\Name|\PhpParser\Node\Identifier|null */
    public $class;
    /** @var list<\PhpParser\Node\AttributeGroup> */
    public $attrGroups;

    /** @return array<int, string> */
    public function getSubNodeNames(): array {}

    public function getStartLine(): int {}
}

namespace PhpParser\Node;

class Stmt extends \PhpParser\Node {}
class Expr extends \PhpParser\Node {}
class Scalar extends \PhpParser\Node {}

class Identifier extends \PhpParser\Node
{
    /** @var string */
    public $name;
    public function __toString(): string {}
    public function toString(): string {}
}

class Name extends \PhpParser\Node
{
    public function __toString(): string {}
    public function toString(): string {}
    public function getLast(): string {}
}

class ComplexType extends \PhpParser\Node {}

class NullableType extends \PhpParser\Node
{
    /** @var \PhpParser\Node */
    public $type;
}

class UnionType extends \PhpParser\Node
{
    /** @var list<\PhpParser\Node> */
    public $types;
}

class MatchArm extends \PhpParser\Node
{
    /** @var list<\PhpParser\Node>|null */
    public $conds;
}

class Attribute extends \PhpParser\Node {}
class AttributeGroup extends \PhpParser\Node
{
    /** @var list<Attribute> */
    public $attrs;
}

class Param extends \PhpParser\Node
{
    /** @var \PhpParser\Node\Expr\Variable */
    public $var;
    /** @var \PhpParser\Node|null */
    public $type;
    /** @var bool */
    public $variadic;
    /** @var int */
    public $flags;
    /** @var \PhpParser\Node|null */
    public $default;
    /** @var bool */
    public $byRef;
}

class Const_ extends \PhpParser\Node
{
    /** @var Identifier */
    public $name;
    /** @var \PhpParser\Node */
    public $value;
}

namespace PhpParser\Node\Stmt;

class ClassLike extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node\Identifier|null */
    public $name;
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class Class_ extends ClassLike {}
class Interface_ extends ClassLike {}
class Trait_ extends ClassLike {}

class ClassMethod extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node\Identifier */
    public $name;
    /** @var list<\PhpParser\Node\Param> */
    public $params;
    /** @var \PhpParser\Node|null */
    public $returnType;
    /** @var list<\PhpParser\Node\Stmt>|null */
    public $stmts;
}

class Function_ extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node\Identifier */
    public $name;
    /** @var list<\PhpParser\Node\Param> */
    public $params;
    /** @var \PhpParser\Node|null */
    public $returnType;
    /** @var list<\PhpParser\Node\Stmt>|null */
    public $stmts;
}

class Property extends \PhpParser\Node\Stmt
{
    /** @var list<\PhpParser\Node\Stmt\PropertyProperty> */
    public $props;
    /** @var \PhpParser\Node|null */
    public $type;
}

class PropertyProperty extends \PhpParser\Node
{
    /** @var \PhpParser\Node\Identifier */
    public $name;
}

class ClassConst extends \PhpParser\Node\Stmt
{
    /** @var list<\PhpParser\Node\Const_> */
    public $consts;
}

class Const_ extends \PhpParser\Node\Stmt
{
    /** @var list<\PhpParser\Node\Const_> */
    public $consts;
}

class Namespace_ extends \PhpParser\Node\Stmt
{
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class Declare_ extends \PhpParser\Node\Stmt
{
    /** @var list<DeclareDeclare> */
    public $declares;
}

class DeclareDeclare extends \PhpParser\Node
{
    /** @var \PhpParser\Node\Identifier */
    public $key;
    /** @var \PhpParser\Node */
    public $value;
}

class Use_ extends \PhpParser\Node\Stmt {}
class GroupUse extends \PhpParser\Node\Stmt {}
class Nop extends \PhpParser\Node\Stmt {}

class InlineHTML extends \PhpParser\Node\Stmt
{
    /** @var string */
    public $value;
}

class Expression extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node\Expr */
    public $expr;
}

class If_ extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node */
    public $cond;
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
    /** @var list<\PhpParser\Node\Stmt\ElseIf_> */
    public $elseifs;
    /** @var \PhpParser\Node\Stmt\Else_|null */
    public $else;
}

class ElseIf_ extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node */
    public $cond;
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class Else_ extends \PhpParser\Node\Stmt
{
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class While_ extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node */
    public $cond;
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class For_ extends \PhpParser\Node\Stmt
{
    /** @var list<\PhpParser\Node> */
    public $cond;
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class Foreach_ extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node */
    public $expr;
    /** @var \PhpParser\Node */
    public $valueVar;
    /** @var \PhpParser\Node|null */
    public $keyVar;
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class Do_ extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node */
    public $cond;
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class Return_ extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node|null */
    public $expr;
}

class Break_ extends \PhpParser\Node\Stmt {}
class Continue_ extends \PhpParser\Node\Stmt {}

class Catch_ extends \PhpParser\Node\Stmt
{
    /** @var list<\PhpParser\Node\Name> */
    public $types;
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class TryCatch extends \PhpParser\Node\Stmt
{
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
    /** @var list<Catch_> */
    public $catches;
    /** @var Finally_|null */
    public $finally;
}

class Finally_ extends \PhpParser\Node\Stmt
{
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class Switch_ extends \PhpParser\Node\Stmt
{
    /** @var list<Case_> */
    public $cases;
}

class Case_ extends \PhpParser\Node\Stmt
{
    /** @var \PhpParser\Node|null */
    public $cond;
    /** @var list<\PhpParser\Node\Stmt> */
    public $stmts;
}

class Throw_ extends \PhpParser\Node\Stmt {}

class Echo_ extends \PhpParser\Node\Stmt
{
    /** @var list<\PhpParser\Node> */
    public $exprs;
}

namespace PhpParser\Node\Expr;

class BinaryOp extends \PhpParser\Node\Expr {}
class Closure extends \PhpParser\Node\Expr {}
class ArrowFunction extends \PhpParser\Node\Expr {}

class Variable extends \PhpParser\Node\Expr
{
    /** @var string|int|null */
    public $name;
}

class PropertyFetch extends \PhpParser\Node\Expr
{
    /** @var \PhpParser\Node\Expr */
    public $var;
    /** @var \PhpParser\Node\Identifier|string|null */
    public $name;
}

class Assign extends \PhpParser\Node\Expr
{
    /** @var \PhpParser\Node\Expr */
    public $var;
    /** @var \PhpParser\Node\Expr */
    public $expr;
}

class AssignOp extends \PhpParser\Node\Expr
{
    /** @var \PhpParser\Node\Expr */
    public $var;
    /** @var \PhpParser\Node\Expr */
    public $expr;
}

class ConstFetch extends \PhpParser\Node\Expr
{
    /** @var \PhpParser\Node\Name */
    public $name;
}

class Ternary extends \PhpParser\Node\Expr {}
class Exit_ extends \PhpParser\Node\Expr {}
class New_ extends \PhpParser\Node\Expr {}

class ClassConstFetch extends \PhpParser\Node\Expr
{
    /** @var \PhpParser\Node\Name|\PhpParser\Node\Identifier|null */
    public $class;
    /** @var \PhpParser\Node\Identifier|null */
    public $name;
}

class Throw_ extends \PhpParser\Node\Expr {}

class MethodCall extends \PhpParser\Node\Expr
{
    /** @var \PhpParser\Node\Expr */
    public $var;
    /** @var \PhpParser\Node\Identifier|string */
    public $name;
    /** @var list<\PhpParser\Node> */
    public $args;
}

class StaticCall extends \PhpParser\Node\Expr
{
    /** @var \PhpParser\Node\Name|\PhpParser\Node\Identifier|null */
    public $class;
    /** @var \PhpParser\Node\Identifier|string */
    public $name;
    /** @var list<\PhpParser\Node> */
    public $args;
}

namespace PhpParser\Node\Expr\BinaryOp;

class Equal extends \PhpParser\Node\Expr\BinaryOp {}
class NotEqual extends \PhpParser\Node\Expr\BinaryOp {}
class Identical extends \PhpParser\Node\Expr\BinaryOp {}
class NotIdentical extends \PhpParser\Node\Expr\BinaryOp {}
class Smaller extends \PhpParser\Node\Expr\BinaryOp {}
class SmallerOrEqual extends \PhpParser\Node\Expr\BinaryOp {}
class Greater extends \PhpParser\Node\Expr\BinaryOp {}
class GreaterOrEqual extends \PhpParser\Node\Expr\BinaryOp {}
class BooleanAnd extends \PhpParser\Node\Expr\BinaryOp {}
class BooleanOr extends \PhpParser\Node\Expr\BinaryOp {}

namespace PhpParser\Node\Scalar;

class String_ extends \PhpParser\Node\Scalar
{
    /** @var string */
    public $value;
}

class LNumber extends \PhpParser\Node\Scalar
{
    /** @var int */
    public $value;
}

class DNumber extends \PhpParser\Node\Scalar
{
    /** @var float */
    public $value;
}

class Int_ extends \PhpParser\Node\Scalar
{
    /** @var int */
    public $value;
}

namespace Millerphp\Readalizer\Analysis;

class Node extends \PhpParser\Node {}

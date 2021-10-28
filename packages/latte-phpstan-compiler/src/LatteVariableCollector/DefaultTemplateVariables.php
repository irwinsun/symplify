<?php

declare(strict_types=1);

namespace Symplify\LattePHPStanCompiler\LatteVariableCollector;

use Nette\Security\User;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Control;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use stdClass;
use Symplify\LattePHPStanCompiler\Contract\LatteVariableCollectorInterface;
use Symplify\TemplatePHPStanCompiler\ValueObject\VariableAndType;

final class DefaultTemplateVariables implements LatteVariableCollectorInterface
{
    /**
     * @return VariableAndType[]
     */
    public function getVariablesAndTypes(): array
    {
        $variablesAndTypes = [];
        $variablesAndTypes[] = new VariableAndType('baseUrl', new StringType());
        $variablesAndTypes[] = new VariableAndType('basePath', new StringType());

        // nette\security bridge
        $variablesAndTypes[] = new VariableAndType('user', new ObjectType(User::class));

        // nette\application bridge
        $variablesAndTypes[] = new VariableAndType('presenter', new ObjectType(Presenter::class));
        $variablesAndTypes[] = new VariableAndType('control', new ObjectType(Control::class));

        $flashesArrayType = new ArrayType(new MixedType(), new ObjectType(stdClass::class));
        $variablesAndTypes[] = new VariableAndType('flashes', $flashesArrayType);

        return $variablesAndTypes;
    }
}

<?php

declare(strict_types=1);

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Composer\ValueObject\ComposerModifier\ChangePackageVersion;
use Rector\Composer\ValueObject\ComposerModifier\RemovePackage;
use Rector\Composer\ValueObject\ComposerModifier\ReplacePackage;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector;
use Rector\Generic\ValueObject\ArgumentDefaultValueReplacer;
use Rector\Nette\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector;
use Rector\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector;
use Rector\Nette\Rector\MethodCall\AddNextrasDatePickerToDateControlRector;
use Rector\Nette\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector;
use Rector\Nette\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector;
use Rector\Nette\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector;
use Rector\Nette\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector;
use Rector\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstant;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/nette-30-dependency-injection.php');
    $containerConfigurator->import(__DIR__ . '/nette-30-return-types.php');
    $containerConfigurator->import(__DIR__ . '/nette-30-param-types.php');

    $services = $containerConfigurator->services();
    $services->set(AddNextrasDatePickerToDateControlRector::class);
    $services->set(ChangeFormArrayAccessToAnnotatedControlVariableRector::class);
    $services->set(MergeDefaultsInGetConfigCompilerExtensionRector::class);
    // Control class has remove __construct(), e.g. https://github.com/Pixidos/GPWebPay/pull/16/files#diff-fdc8251950f85c5467c63c249df05786
    $services->set(RemoveParentCallWithoutParentRector::class);
    // https://github.com/nette/utils/commit/d0041ba59f5d8bf1f5b3795fd76d43fb13ea2e15
    $services->set(FormerNullableArgumentToScalarTypedRector::class);
    $services->set(StaticCallToMethodCallRector::class)->call('configure', [[
        StaticCallToMethodCallRector::STATIC_CALLS_TO_METHOD_CALLS => ValueObjectInliner::inline([
            new StaticCallToMethodCall('Nette\Security\Passwords', 'hash', 'Nette\Security\Passwords', 'hash'),
            new StaticCallToMethodCall('Nette\Security\Passwords', 'verify', 'Nette\Security\Passwords', 'verify'),
            new StaticCallToMethodCall(
                'Nette\Security\Passwords',
                'needsRehash',
                'Nette\Security\Passwords',
                'needsRehash'
            ),
        ]),
    ]]);
    // https://github.com/contributte/event-dispatcher-extra/tree/v0.4.3 and higher
    $services->set(RenameClassConstantRector::class)->call('configure', [[
        RenameClassConstantRector::CLASS_CONSTANT_RENAME => ValueObjectInliner::inline([
            new RenameClassConstant('Contributte\Events\Extra\Event\Security\LoggedInEvent', 'NAME', 'class'),
            new RenameClassConstant('Contributte\Events\Extra\Event\Security\LoggedOutEvent', 'NAME', 'class'),
            new RenameClassConstant('Contributte\Events\Extra\Event\Application\ShutdownEvent', 'NAME', 'class'),
        ]),
    ]]);
    $services->set(RenameClassRector::class)->call('configure', [[
        RenameClassRector::OLD_TO_NEW_CLASSES => [
            # nextras/forms was split into 2 packages
            'Nextras\FormComponents\Controls\DatePicker' => 'Nextras\FormComponents\Controls\DateControl',
            # @see https://github.com/nette/di/commit/a0d361192f8ac35f1d9f82aab7eb351e4be395ea
            'Nette\DI\ServiceDefinition' => 'Nette\DI\Definitions\ServiceDefinition',
            'Nette\DI\Statement' => 'Nette\DI\Definitions\Statement',
            'WebChemistry\Forms\Controls\Multiplier' => 'Contributte\FormMultiplier\Multiplier',
        ],
    ]]);
    $services->set(ArgumentDefaultValueReplacerRector::class)->call('configure', [[
        ArgumentDefaultValueReplacerRector::REPLACED_ARGUMENTS => ValueObjectInliner::inline([
            // json 2nd argument is now `int` typed
            new ArgumentDefaultValueReplacer('Nette\Utils\Json', 'decode', 1, true, 'Nette\Utils\Json::FORCE_ARRAY'),
            // @see https://github.com/nette/forms/commit/574b97f9d5e7a902a224e57d7d584e7afc9fefec
            new ArgumentDefaultValueReplacer('Nette\Forms\Form', 'decode', 0, true, 'array'),
        ]),
    ]]);
    $services->set(RenameMethodRector::class)->call('configure', [[
        RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
            // see https://github.com/nette/forms/commit/b99385aa9d24d729a18f6397a414ea88eab6895a
            new MethodCallRename('Nette\Forms\Controls\BaseControl', 'setType', 'setHtmlType'),
            new MethodCallRename('Nette\Forms\Controls\BaseControl', 'setAttribute', 'setHtmlAttribute'),
            new MethodCallRename(
                'Nette\DI\Definitions\ServiceDefinition',
                # see https://github.com/nette/di/commit/1705a5db431423fc610a6f339f88dead1b5dc4fb
                'setClass',
                'setType'
            ),
            new MethodCallRename('Nette\DI\Definitions\ServiceDefinition', 'getClass', 'getType'),
            new MethodCallRename('Nette\DI\Definitions\Definition', 'isAutowired', 'getAutowired'),
        ]),
    ]]);
    $services->set(MagicHtmlCallToAppendAttributeRector::class);
    $services->set(RequestGetCookieDefaultArgumentToCoalesceRector::class);
    $services->set(RemoveParentAndNameFromComponentConstructorRector::class);
    $services->set(MoveFinalGetUserToCheckRequirementsClassMethodRector::class);
    $services->set(ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector::class);

    $services->set(ComposerModifier::class)
        ->call('configure', [
            ValueObjectInliner::inline([
                // meta package
                new ChangePackageVersion('nette/nette', '^3.0'),
                // https://github.com/nette/nette/blob/v2.4.0/composer.json vs https://github.com/nette/nette/blob/v3.0.0/composer.json
                // older versions have security issues
                new ChangePackageVersion('nette/application', '^3.0.6'),
                new ChangePackageVersion('nette/bootstrap', '^3.0'),
                new ChangePackageVersion('nette/caching', '^3.0'),
                new ChangePackageVersion('nette/component-model', '^3.0'),
                new ChangePackageVersion('nette/database', '^3.0'),
                new RemovePackage('nette/deprecated'),
                new ChangePackageVersion('nette/di', '^3.0'),
                new ChangePackageVersion('nette/finder', '^2.5'),
                new ChangePackageVersion('nette/forms', '^3.0'),
                new ChangePackageVersion('nette/http', '^3.0'),
                new ChangePackageVersion('nette/mail', '^3.0'),
                new ChangePackageVersion('nette/neon', '^3.0'),
                new ChangePackageVersion('nette/php-generator', '^3.0'),
                new RemovePackage('nette/reflection'),
                new ChangePackageVersion('nette/robot-loader', '^3.0'),
                new ChangePackageVersion('nette/safe-stream', '^2.4'),
                new ChangePackageVersion('nette/security', '^3.0'),
                new ChangePackageVersion('nette/tokenizer', '^3.0'),
                new ChangePackageVersion('nette/utils', '^3.0'),
                new ChangePackageVersion('latte/latte', '^2.5'),
                new ChangePackageVersion('tracy/tracy', '^2.6'),
                // webchemistry to contributte
                new ReplacePackage('webchemistry/forms-multiplier', 'contributte/forms-multiplier', '3.1.x-dev'),
                // contributte packages
                new ChangePackageVersion('contributte/event-dispatcher-extra', '^0.8'),
                new ChangePackageVersion('contributte/forms-multiplier', '3.1.x-dev'),
                // other packages
                new ChangePackageVersion('radekdostal/nette-datetimepicker', '^3.0'),
            ]),
        ]);
};

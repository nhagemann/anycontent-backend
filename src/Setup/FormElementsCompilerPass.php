<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\DependencyInjection\ServiceTags;
use AnyContent\Backend\Services\FormManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Find all form elements and register within FormManager during container compilation
 */
class FormElementsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $necessaryServiceClass = FormManager::class;

        // first check if the necessary service is defined
        if (!$container->has($necessaryServiceClass)) {
            return;
        }

        $definition = $container->findDefinition($necessaryServiceClass);
        $formElementClasses = array_keys($container->findTaggedServiceIds(ServiceTags::FORM_ELEMENT));
        $customFormElementClasses = array_keys($container->findTaggedServiceIds(ServiceTags::CUSTOM_FORM_ELEMENT));

        foreach ($formElementClasses as $className) {
            if (in_array($className, $customFormElementClasses)) {
                $definition->addMethodCall('registerCustomFormElement', [new Reference($className)]);
                continue;
            }

            $definition->addMethodCall('registerFormElement', [new Reference($className)]);
        }
    }
}

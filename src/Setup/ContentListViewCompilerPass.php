<?php

namespace AnyContent\Backend\Setup;

use AnyContent\Backend\DependencyInjection\ServiceTags;
use AnyContent\Backend\Services\ContentListViewsManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ContentListViewCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $necessaryServiceClass = ContentListViewsManager::class;
        // first check if the necessary service is defined
        if (!$container->has($necessaryServiceClass)) {
            return;
        }

        $definition = $container->findDefinition($necessaryServiceClass);
        $taggedServices = $container->findTaggedServiceIds(ServiceTags::CONTENT_LIST_VIEW);

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('registerContentView', [new Reference($id)]);
        }
    }
}

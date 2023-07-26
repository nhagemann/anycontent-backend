<?php

namespace AnyContent\Backend\DependencyInjection;

use AnyContent\Backend\ContentListViews\ContentListViewInterface;
use AnyContent\Backend\Setup\FormElementsAdder;
use AnyContent\Backend\Setup\RepositoryAdder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AnyContentBackendExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        // @see https://stackoverflow.com/questions/65765693/symfony-bundle-access-config-value
        $service = $container->getDefinition(RepositoryAdder::class);
        $service->setArgument('$connections', $config['connections']);

        $service = $container->getDefinition(FormElementsAdder::class);
        $service->setArgument('$formElements', $config['formelements']);

        // Tag Content List Views
        $container->registerForAutoconfiguration(ContentListViewInterface::class)->addTag(ServiceTags::CONTENT_LIST_VIEW);
    }
}

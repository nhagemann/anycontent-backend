<?php

namespace AnyContent\Backend\DependencyInjection;

use AnyContent\Backend\ContentListViews\ContentListViewInterface;
use AnyContent\Backend\Forms\FormElements\CustomFormElementInterface;
use AnyContent\Backend\Forms\FormElements\FormElementInterface;
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

        // tag content list views and (custom) form elements for auto registration
        $container->registerForAutoconfiguration(ContentListViewInterface::class)->addTag(ServiceTags::CONTENT_LIST_VIEW);
        $container->registerForAutoconfiguration(FormElementInterface::class)->addTag(ServiceTags::FORM_ELEMENT);
        $container->registerForAutoconfiguration(CustomFormElementInterface::class)->addTag(ServiceTags::CUSTOM_FORM_ELEMENT);
    }
}

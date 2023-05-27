Module::EVENT_LISTING_RENDER event.listing.render

$event = new ListingRenderEvent($app, $template, $vars);
$event = $app['dispatcher']->dispatch(Module::EVENT_LISTING_RENDER, $event);



$event = new LayoutTemplateRenderEvent($app, $templateFilename, $vars);

/** @var LayoutTemplateRenderEvent $event */
$event = $app['dispatcher']->dispatch(Module::EVENT_LAYOUT_TEMPLATE_RENDER, $event);
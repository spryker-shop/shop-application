<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ShopApplication\Plugin\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Spryker\Shared\Kernel\Communication\Application as SprykerApplication;
use Spryker\Yves\Kernel\AbstractPlugin;
use SprykerShop\Yves\ShopApplication\Exception\InvalidApplicationException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @deprecated Use {@link \SprykerShop\Yves\ShopApplication\Plugin\EventDispatcher\ShopApplicationFilterControllerEventDispatcherPlugin} instead.
 *
 * @method \SprykerShop\Yves\ShopApplication\ShopApplicationFactory getFactory()
 * @method \SprykerShop\Yves\ShopApplication\ShopApplicationConfig getConfig()
 */
class ShopControllerEventServiceProvider extends AbstractPlugin implements ServiceProviderInterface
{
    /**
     * @param \Silex\Application $app
     *
     * @return void
     */
    public function register(Application $app)
    {
    }

    /**
     * @param \Silex\Application $app
     *
     * @throws \SprykerShop\Yves\ShopApplication\Exception\InvalidApplicationException
     *
     * @return void
     */
    public function boot(Application $app)
    {
        if (!$app instanceof SprykerApplication) {
            throw new InvalidApplicationException(sprintf(
                'The used application object need to be an instance of %s.',
                SprykerApplication::class,
            ));
        }

        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $appDispatcher */
        $appDispatcher = $app['dispatcher'];
        $appDispatcher->addListener(KernelEvents::CONTROLLER, function (ControllerEvent $event) use ($app) {
            $this->onKernelController($event, $app);
        }, 0);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
     * @param \Spryker\Shared\Kernel\Communication\Application $application
     *
     * @return void
     */
    public function onKernelController(ControllerEvent $event, SprykerApplication $application)
    {
        foreach ($this->getFactory()->getFilterControllerEventSubscriberPlugins() as $filterControllerEventListenerPlugin) {
            $filterControllerEventListenerPlugin->handle($event);
        }
    }
}

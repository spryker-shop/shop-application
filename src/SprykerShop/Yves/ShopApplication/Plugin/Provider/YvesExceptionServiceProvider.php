<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ShopApplication\Plugin\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @deprecated Use {@link \SprykerShop\Yves\ShopApplication\Plugin\EventDispatcher\ShopApplicationExceptionEventDispatcherPlugin} instead.
 *
 * @method \Spryker\Yves\Application\ApplicationFactory getFactory()
 */
class YvesExceptionServiceProvider extends AbstractPlugin implements ServiceProviderInterface
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
     * @return void
     */
    public function boot(Application $app)
    {
        /** @var \Symfony\Component\EventDispatcher\EventDispatcher $appDispatcher */
        $appDispatcher = $app['dispatcher'];
        $appDispatcher->addListener(KernelEvents::EXCEPTION, [$this, 'onKernelException'], -8);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     *
     * @throws \Exception
     *
     * @return void
     */
    public function onKernelException(ExceptionEvent $event)
    {
        throw $event->getThrowable();
    }
}

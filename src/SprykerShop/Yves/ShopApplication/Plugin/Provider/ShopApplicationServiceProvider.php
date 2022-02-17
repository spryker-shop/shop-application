<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\ShopApplication\Plugin\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Spryker\Shared\Application\ApplicationConstants;
use Spryker\Shared\Config\Application\Environment as ApplicationEnvironment;
use Spryker\Shared\Config\Config;
use Spryker\Shared\Kernel\Store;
use Spryker\Shared\Log\LogConstants;
use Spryker\Yves\Kernel\AbstractPlugin;
use Spryker\Yves\Kernel\ControllerResolver\YvesFragmentControllerResolver;
use Spryker\Yves\Kernel\Plugin\Pimple;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated There are four classes created as replacement for current one.
 * @see \SprykerShop\Yves\ShopApplication\Plugin\Twig\ShopApplicationTwigPlugin
 * @see \SprykerShop\Yves\ShopApplication\Plugin\Application\ShopApplicationApplicationPlugin
 * @see \Spryker\Yves\Store\Plugin\Application\StoreApplicationPlugin
 * @see \Spryker\Yves\Locale\Plugin\Application\LocaleApplicationPlugin
 *
 * @method \SprykerShop\Yves\ShopApplication\ShopApplicationFactory getFactory()
 * @method \SprykerShop\Yves\ShopApplication\ShopApplicationConfig getConfig()
 */
class ShopApplicationServiceProvider extends AbstractPlugin implements ServiceProviderInterface
{
    /**
     * @var string
     */
    public const LOCALE = 'locale';

    /**
     * @var string
     */
    public const STORE = 'store';

    /**
     * @var string
     */
    public const REQUEST_URI = 'REQUEST_URI';

    /**
     * @var \Spryker\Shared\Kernel\Communication\Application
     */
    protected $application;

    /**
     * @param \Spryker\Shared\Kernel\Communication\Application $app
     *
     * @return void
     */
    public function register(Application $app)
    {
        $this->application = $app;

        $this->setPimpleApplication();
        $this->setDebugMode();
        $this->setControllerResolver();
        $this->setLocale();
        $this->setStore();
        $this->setLogLevel();

        $this->addGlobalTemplateVariables($app, [
            'environment' => $this->getConfig()->getTwigEnvironmentName(),
        ]);
    }

    /**
     * @param \Spryker\Shared\Kernel\Communication\Application $app
     *
     * @return void
     */
    public function boot(Application $app)
    {
    }

    /**
     * @return void
     */
    protected function setPimpleApplication()
    {
        $pimplePlugin = new Pimple();
        $pimplePlugin->setApplication($this->application);
    }

    /**
     * @return void
     */
    protected function setDebugMode()
    {
        $this->application['debug'] = Config::get(ApplicationConstants::ENABLE_APPLICATION_DEBUG, false);
    }

    /**
     * @return void
     */
    protected function setControllerResolver()
    {
        $this->application['resolver'] = $this->application->share(function () {
            return new YvesFragmentControllerResolver($this->application);
        });
    }

    /**
     * @return void
     */
    protected function setLocale()
    {
        $localeClient = $this->getFactory()->getLocaleClient();
        $this->application[static::LOCALE] = $localeClient->getCurrentLocale();

        $requestUri = $this->getRequestUri();

        if ($requestUri) {
            /** @var array<string> $pathElements */
            $pathElements = explode('/', trim($requestUri, '/'));
            $identifier = $pathElements[0];
            $locales = $localeClient->getLocales();
            if (array_key_exists($identifier, $locales)) {
                $currentLocale = $locales[$identifier];
                $this->application[static::LOCALE] = $currentLocale;
                ApplicationEnvironment::initializeLocale($currentLocale);
            }
        }
    }

    /**
     * @return void
     */
    protected function setStore()
    {
        $store = Store::getInstance();

        $this->application[static::STORE] = $store->getStoreName();
    }

    /**
     * @return void
     */
    protected function setLogLevel()
    {
        $this->application['monolog.level'] = Config::get(LogConstants::LOG_LEVEL);
    }

    /**
     * @return string
     */
    protected function getRequestUri()
    {
        $requestUri = Request::createFromGlobals()
            ->server->get(static::REQUEST_URI);

        return $requestUri;
    }

    /**
     * @param \Spryker\Shared\Kernel\Communication\Application $app
     * @param array $globalTemplateVariables
     *
     * @return void
     */
    protected function addGlobalTemplateVariables(Application $app, array $globalTemplateVariables)
    {
        $app['twig.global.variables'] = $app->share(
            $app->extend('twig.global.variables', function (array $variables) use ($globalTemplateVariables) {
                return array_merge($variables, $globalTemplateVariables);
            }),
        );
    }
}

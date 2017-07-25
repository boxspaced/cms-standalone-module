<?php
namespace Boxspaced\CmsStandaloneModule\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Boxspaced\CmsStandaloneModule\Controller\StandaloneController;
use Boxspaced\CmsStandaloneModule\Service\StandaloneService;
use Boxspaced\CmsAccountModule\Service\AccountService;
use Zend\Log\Logger;
use Boxspaced\CmsCoreModule\Controller\AbstractControllerFactory;

class StandaloneControllerFactory extends AbstractControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new StandaloneController(
            $container->get(StandaloneService::class),
            $container->get(AccountService::class),
            $container->get(Logger::class),
            $container->get('config')
        );

        $this->adminNavigationWidget($controller);

        return $this->forceHttps($controller, $container);
    }

}

<?php
namespace Standalone\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Standalone\Controller\StandaloneController;
use Standalone\Service\StandaloneService;
use Account\Service\AccountService;
use Zend\Log\Logger;
use Core\Controller\AbstractControllerFactory;

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

        return $this->forceHttps($controller, $container);
    }

}

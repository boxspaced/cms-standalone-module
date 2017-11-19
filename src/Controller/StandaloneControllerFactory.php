<?php
namespace Boxspaced\CmsStandaloneModule\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Boxspaced\CmsStandaloneModule\Controller\StandaloneController;
use Boxspaced\CmsStandaloneModule\Service\StandaloneService;
use Boxspaced\CmsAccountModule\Service\AccountService;
use Zend\Log\Logger;

class StandaloneControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new StandaloneController(
            $container->get(StandaloneService::class),
            $container->get(AccountService::class),
            $container->get(Logger::class),
            $container->get('config')
        );
    }

}

<?php
namespace Standalone\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Log\Logger;
use Zend\Paginator;
use Standalone\Service;
use Account\Service\AccountService;
use Standalone\Service\StandaloneService;

class StandaloneController extends AbstractActionController
{

    /**
     * @var StandaloneService
     */
    protected $standaloneService;

    /**
     * @var AccountService
     */
    protected $accountService;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ViewModel
     */
    protected $view;

    /**
     * @param StandaloneService $standaloneService
     * @param AccountService $accountService
     * @param Logger $logger
     * @param array $config
     */
    public function __construct(
        StandaloneService $standaloneService,
        AccountService $accountService,
        Logger $logger,
        array $config
    )
    {
        $this->standaloneService = $standaloneService;
        $this->accountService = $accountService;
        $this->logger = $logger;
        $this->config = $config;

        $this->view = new ViewModel();
        $this->view->setTerminal(true);
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $adminNavigation = $this->adminNavigationWidget();
        if (null !== $adminNavigation) {
            $this->view->addChild($adminNavigation, 'adminNavigation');
        }

        $adapter = new Paginator\Adapter\Callback(
            function ($offset, $itemCountPerPage) {
                return $this->standaloneService->getPublishedStandalone($offset, $itemCountPerPage);
            },
            function () {
                return $this->standaloneService->countPublishedStandalone();
            }
        );

        $paginator = new Paginator\Paginator($adapter);
        $paginator->setCurrentPageNumber($this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage($this->config['core']['admin_show_per_page']);
        $this->view->paginator = $paginator;

        $standaloneItems = [];

        foreach ($paginator as $item) {

            // @todo remove, should be view helpers
            $lifespanState = $this->itemAdminWidget()->calcLifeSpanState($item->liveFrom, $item->expiresEnd);
            $lifespanTitle = $this->itemAdminWidget()->calcLifeSpanTitle($item->liveFrom, $item->expiresEnd);

            $standaloneItems[] = array(
                'typeIcon' => $item->typeIcon,
                'typeName' => $item->typeName,
                'moduleName' => $item->moduleName,
                'name' => $item->name,
                'id' => $item->id,
                'lifespanState' => $lifespanState,
                'lifespanTitle' => $lifespanTitle,
                'allowEdit' => $this->accountService->isAllowed($item->moduleName, 'edit'),
                'allowPublish' => $this->accountService->isAllowed($item->moduleName, 'publish'),
                'allowDelete' => $this->accountService->isAllowed($item->moduleName, 'delete'),
            );
        }

        $this->view->standaloneItems = $standaloneItems;

        $this->view->allowCreateItem = $this->accountService->isAllowed('item', 'create');

        return $this->view;
    }

}

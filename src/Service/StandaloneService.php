<?php
namespace Boxspaced\CmsStandaloneModule\Service;

use Boxspaced\EntityManager\EntityManager;
use Zend\Authentication\AuthenticationService;
use Zend\Log\Logger;
use Zend\Db\Sql;
use Boxspaced\CmsAccountModule\Model\UserRepository;
use Boxspaced\CmsAccountModule\Model\User;
use Boxspaced\CmsItemModule\Model\Item;
use Boxspaced\CmsVersioningModule\Model\VersionableInterface;
use Boxspaced\CmsItemModule\Service\ItemService;

class StandaloneService
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @param AuthenticationService $authService
     * @param EntityManager $entityManager
     * @param UserRepository $userRepository
     */
    public function __construct(
        Logger $logger,
        AuthenticationService $authService,
        EntityManager $entityManager,
        UserRepository $userRepository
    )
    {
        $this->logger = $logger;
        $this->authService = $authService;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;

        if ($this->authService->hasIdentity()) {
            $identity = $authService->getIdentity();
            $this->user = $userRepository->getById($identity->id);
        }
    }

    /**
     * @param int $offset
     * @param int $showPerPage
     * @return StandaloneContent[]
     */
    public function getPublishedStandalone($offset = null, $showPerPage = null)
    {
        $select = $this->createPublishedSelect();
        $select->order(['name' => 'ASC']);

        if (null !== $offset && null !== $showPerPage) {
            $select->limit($showPerPage)->offset($offset);
        }

        $sql = new Sql\Sql($this->entityManager->getDb());
        $stmt = $sql->prepareStatementForSqlObject($select);

        $items = [];

        foreach ($stmt->execute()->getResource()->fetchAll() as $row) {

            $item = $this->entityManager->find(Item::class, $row['id']);

            $standaloneContent = new StandaloneContent();
            $standaloneContent->id = $item->getId();
            $standaloneContent->name = $item->getRoute()->getSlug();
            $standaloneContent->typeIcon = $item->getType()->getIcon();
            $standaloneContent->typeName = $item->getType()->getName();
            $standaloneContent->moduleName = $item->getRoute()->getModule()->getRouteController();
            $standaloneContent->liveFrom = $item->getLiveFrom();
            $standaloneContent->expiresEnd = $item->getExpiresEnd();
            $standaloneContent->navText = $item->getNavText();

            $items[] = $standaloneContent;
        }

        return $items;
    }

    /**
     * @return int
     */
    public function countPublishedStandalone()
    {
        $select = $this->createPublishedSelect();

        $select->columns([
            'count' => new Sql\Expression('COUNT(*)'),
        ]);

        $sql = new Sql\Sql($this->entityManager->getDb());
        $stmt = $sql->prepareStatementForSqlObject($select);

        return (int) $stmt->execute()->getResource()->fetchColumn();
    }

    /**
     * @return Sql\Select
     */
    protected function createPublishedSelect()
    {
        $platform = $this->entityManager->getDb()->getPlatform();

        $select = new Sql\Select();

        $select->columns([
            'type' => new Sql\Literal($platform->quoteValue('Item')),
            'id',
        ]);

        $select->from('item');
        $select->join('route', 'route.id = item.route_id', [
            'name' => 'slug',
        ]);

        $select->where([
            'published_to = ?' => ItemService::PUBLISH_TO_STANDALONE,
            'status = ?' => VersionableInterface::STATUS_PUBLISHED,
        ]);

        return (new Sql\Select())->from(['all' => $select]);
    }

}

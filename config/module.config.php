<?php
namespace Standalone;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            // LIFO
            'standalone' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/standalone[/:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\StandaloneController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            // LIFO
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\StandaloneService::class => Service\StandaloneServiceFactory::class,
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\StandaloneController::class => Controller\StandaloneControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];

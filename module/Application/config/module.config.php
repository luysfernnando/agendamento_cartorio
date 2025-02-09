<?php

declare(strict_types=1);

namespace Application;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'api' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/api',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'users' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/users[/:id]',
                            'defaults' => [
                                'controller' => Controller\Api\UserController::class,
                            ],
                        ],
                    ],
                    'services' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/services[/:id][/:action]',
                            'constraints' => [
                                'id' => '[0-9]+',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                'controller' => Controller\Api\ServiceController::class,
                            ],
                        ],
                    ],
                    'appointments' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/appointments[/:id]',
                            'defaults' => [
                                'controller' => Controller\Api\AppointmentController::class,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\Api\UserController::class => Factory\Controller\Api\UserControllerFactory::class,
            Controller\Api\ServiceController::class => Factory\Controller\Api\ServiceControllerFactory::class,
            Controller\Api\AppointmentController::class => Factory\Controller\Api\AppointmentControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\UserService::class => Factory\Service\UserServiceFactory::class,
            Service\ServiceService::class => Factory\Service\ServiceServiceFactory::class,
            Service\AppointmentService::class => Factory\Service\AppointmentServiceFactory::class,
            Service\NotificationService::class => Factory\Service\NotificationServiceFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],
];

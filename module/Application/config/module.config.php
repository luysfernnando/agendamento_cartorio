<?php

declare(strict_types=1);

namespace Application;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\Session as SessionStorage;
use Application\Controller\AppointmentController;
use Application\Factory\Controller\AppointmentControllerFactory;
use Application\Factory\Form\AppointmentFormFactory;
use Application\Form\AppointmentForm;
use Application\Controller\AdminController;
use Application\Controller\AdminControllerFactory;
use Application\Controller\AuthController;
use Application\Controller\AuthControllerFactory;
use Application\Controller\Factory\IndexControllerFactory;
use Application\Controller\IndexController;
use Application\Form\LoginForm;
use Application\Form\RegisterForm;
use Application\Service\AppointmentService;
use Application\Service\AppointmentServiceFactory;
use Application\Service\AuthService;
use Application\Service\AuthServiceFactory;
use Application\Service\NotificationService;
use Application\Service\NotificationServiceFactory;
use Application\Service\ServiceService;
use Application\Service\ServiceServiceFactory;
use Application\Service\UserService;
use Application\Service\UserServiceFactory;
use Application\View\Helper\AppointmentStatusColor;
use Application\Command\CreateAdminUserCommand;
use Application\Command\Factory\CreateAdminUserCommandFactory;

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
            'login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'login',
                    ],
                ],
            ],
            'register' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/register',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'register',
                    ],
                ],
            ],
            'logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
            'profile' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/profile[/:action]',
                    'defaults' => [
                        'controller' => Controller\ProfileController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'auth' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/auth[/:action]',
                    'defaults' => [
                        'controller' => AuthController::class,
                        'action' => 'login',
                    ],
                ],
            ],
            'appointment' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/appointment[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AppointmentController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'appointments' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/appointments',
                    'defaults' => [
                        'controller' => Controller\AppointmentController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'index',
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
            Controller\IndexController::class => Factory\Controller\IndexControllerFactory::class,
            Controller\AuthController::class => Factory\Controller\AuthControllerFactory::class,
            Controller\AppointmentController::class => Factory\Controller\AppointmentControllerFactory::class,
            Controller\AdminController::class => Factory\Controller\AdminControllerFactory::class,
            Controller\ProfileController::class => Factory\Controller\ProfileControllerFactory::class,
            Controller\Api\UserController::class => Factory\Controller\Api\UserControllerFactory::class,
            Controller\Api\ServiceController::class => Factory\Controller\Api\ServiceControllerFactory::class,
            Controller\Api\AppointmentController::class => Factory\Controller\Api\AppointmentControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\UserService::class => Service\Factory\UserServiceFactory::class,
            AuthenticationService::class => Factory\Service\AuthenticationServiceFactory::class,
            AuthService::class => Factory\Service\AuthServiceFactory::class,
            ServiceService::class => Factory\Service\ServiceServiceFactory::class,
            AppointmentService::class => Factory\Service\AppointmentServiceFactory::class,
            NotificationService::class => Factory\Service\NotificationServiceFactory::class,
            Form\LoginForm::class => InvokableFactory::class,
            Form\RegisterForm::class => InvokableFactory::class,
            Form\AppointmentForm::class => Factory\Form\AppointmentFormFactory::class,
            Cache\DoctrineArrayCache::class => Cache\Factory\DoctrineArrayCacheFactory::class,
            Command\CreateAdminUserCommand::class => Command\Factory\CreateAdminUserCommandFactory::class,
        ],
        'aliases' => [
            'doctrine.cache.array' => Cache\DoctrineArrayCache::class,
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
    'view_helpers' => [
        'factories' => [
            View\Helper\AppointmentStatusColor::class => InvokableFactory::class,
        ],
        'aliases' => [
            'appointmentStatusColor' => View\Helper\AppointmentStatusColor::class,
        ],
    ],
    'doctrine' => [
        'driver' => [
            'application_entities' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    'Application\Entity' => 'application_entities'
                ]
            ]
        ]
    ],
    'laminas-cli' => [
        'commands' => [
            'app:create-admin' => Command\CreateAdminUserCommand::class,
        ],
    ],
];

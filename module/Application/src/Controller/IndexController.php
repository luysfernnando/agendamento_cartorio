<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\ServiceService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    private ServiceService $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function indexAction()
    {
        $services = $this->serviceService->getAvailableServices();

        return new ViewModel([
            'services' => $services
        ]);
    }
}

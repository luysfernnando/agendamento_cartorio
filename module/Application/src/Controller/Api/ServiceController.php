<?php

declare(strict_types=1);

namespace Application\Controller\Api;

use Application\Service\ServiceService;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;

class ServiceController extends AbstractRestfulController
{
    private ServiceService $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function create($data)
    {
        try {
            $this->validateServiceData($data);
            $service = $this->serviceService->createService($data);
            
            return new JsonModel([
                'success' => true,
                'data' => $service->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function get($id)
    {
        $service = $this->serviceService->getService((int) $id);
        if (!$service) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
            return new JsonModel([
                'success' => false,
                'message' => 'Serviço não encontrado',
            ]);
        }

        return new JsonModel([
            'success' => true,
            'data' => $service->toArray(),
        ]);
    }

    public function update($id, $data)
    {
        try {
            $service = $this->serviceService->updateService((int) $id, $data);
            if (!$service) {
                $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
                return new JsonModel([
                    'success' => false,
                    'message' => 'Serviço não encontrado',
                ]);
            }

            return new JsonModel([
                'success' => true,
                'data' => $service->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete($id)
    {
        $result = $this->serviceService->deleteService((int) $id);
        if (!$result) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
            return new JsonModel([
                'success' => false,
                'message' => 'Serviço não encontrado',
            ]);
        }

        return new JsonModel([
            'success' => true,
            'message' => 'Serviço excluído com sucesso',
        ]);
    }

    public function getList()
    {
        $showInactive = $this->params()->fromQuery('show_inactive', false);
        $criteria = [];
        
        if (!$showInactive) {
            $criteria['active'] = true;
        }

        $services = $this->serviceService->listServices($criteria);
        return new JsonModel([
            'success' => true,
            'data' => array_map(fn($service) => $service->toArray(), $services),
        ]);
    }

    public function toggleStatusAction()
    {
        $id = $this->params()->fromRoute('id');
        if (!$id) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_400);
            return new JsonModel([
                'success' => false,
                'message' => 'ID do serviço não fornecido',
            ]);
        }

        $service = $this->serviceService->toggleServiceStatus((int) $id);
        if (!$service) {
            $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
            return new JsonModel([
                'success' => false,
                'message' => 'Serviço não encontrado',
            ]);
        }

        return new JsonModel([
            'success' => true,
            'data' => $service->toArray(),
            'message' => 'Status do serviço alterado com sucesso',
        ]);
    }

    private function validateServiceData(array $data): void
    {
        $requiredFields = ['name', 'duration', 'price'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new \InvalidArgumentException(
                'Os seguintes campos são obrigatórios: ' . implode(', ', $missingFields)
            );
        }

        if (isset($data['duration']) && (!is_numeric($data['duration']) || $data['duration'] <= 0)) {
            throw new \InvalidArgumentException('A duração deve ser um número positivo');
        }

        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            throw new \InvalidArgumentException('O preço deve ser um número não negativo');
        }
    }
} 
<?php

declare(strict_types=1);

namespace Application\Form;

use Application\Service\ServiceService;
use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class AppointmentForm extends Form implements InputFilterProviderInterface
{
    private ServiceService $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        parent::__construct('appointment-form');
        $this->serviceService = $serviceService;

        $this->add([
            'name' => 'service_id',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Serviço',
                'value_options' => $this->getServicesOptions(),
                'empty_option' => 'Selecione um serviço',
            ],
            'attributes' => [
                'id' => 'service_id',
                'class' => 'form-select',
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'appointment_date',
            'type' => Element\Date::class,
            'options' => [
                'label' => 'Data',
            ],
            'attributes' => [
                'id' => 'appointment_date',
                'class' => 'form-control',
                'required' => true,
                'min' => date('Y-m-d'),
                'max' => date('Y-m-d', strtotime('+3 months')),
            ],
        ]);

        $this->add([
            'name' => 'appointment_time',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Horário',
                'empty_option' => 'Selecione um horário',
            ],
            'attributes' => [
                'id' => 'appointment_time',
                'class' => 'form-select',
                'required' => true,
                'disabled' => true,
            ],
        ]);

        $this->add([
            'name' => 'notes',
            'type' => Element\Textarea::class,
            'options' => [
                'label' => 'Observações',
            ],
            'attributes' => [
                'id' => 'notes',
                'class' => 'form-control',
                'rows' => 3,
                'placeholder' => 'Adicione observações se necessário',
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Agendar',
                'class' => 'btn btn-primary w-100',
            ],
        ]);
    }

    private function getServicesOptions(): array
    {
        $services = $this->serviceService->getAvailableServices();
        $options = [];

        foreach ($services as $service) {
            $options[$service->getId()] = sprintf(
                '%s - %s - %d minutos',
                $service->getName(),
                'R$ ' . number_format($service->getPrice(), 2, ',', '.'),
                $service->getDuration()
            );
        }

        return $options;
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'service_id' => [
                'required' => true,
                'filters' => [
                    ['name' => 'ToInt'],
                ],
                'validators' => [
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => array_keys($this->getServicesOptions()),
                            'messages' => [
                                'notInArray' => 'Serviço inválido',
                            ],
                        ],
                    ],
                ],
            ],
            'appointment_date' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'Date',
                        'options' => [
                            'format' => 'Y-m-d',
                            'messages' => [
                                'dateFalseFormat' => 'Data inválida',
                            ],
                        ],
                    ],
                    [
                        'name' => 'GreaterThan',
                        'options' => [
                            'min' => date('Y-m-d'),
                            'inclusive' => true,
                            'messages' => [
                                'notGreaterThan' => 'A data deve ser igual ou posterior a hoje',
                            ],
                        ],
                    ],
                    [
                        'name' => 'LessThan',
                        'options' => [
                            'max' => date('Y-m-d', strtotime('+3 months')),
                            'inclusive' => true,
                            'messages' => [
                                'notLessThan' => 'A data deve ser dentro dos próximos 3 meses',
                            ],
                        ],
                    ],
                ],
            ],
            'appointment_time' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'Regex',
                        'options' => [
                            'pattern' => '/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/',
                            'messages' => [
                                'regexNotMatch' => 'Horário inválido',
                            ],
                        ],
                    ],
                ],
            ],
            'notes' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'max' => 500,
                            'messages' => [
                                'stringLengthTooLong' => 'As observações devem ter no máximo 500 caracteres',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
} 
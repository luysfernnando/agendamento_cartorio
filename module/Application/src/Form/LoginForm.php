<?php

declare(strict_types=1);

namespace Application\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class LoginForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('login-form');

        $this->add([
            'name' => 'email',
            'type' => Element\Email::class,
            'options' => [
                'label' => 'E-mail',
            ],
            'attributes' => [
                'id' => 'email',
                'class' => 'form-control',
                'placeholder' => 'Digite seu e-mail',
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'password',
            'type' => Element\Password::class,
            'options' => [
                'label' => 'Senha',
            ],
            'attributes' => [
                'id' => 'password',
                'class' => 'form-control',
                'placeholder' => 'Digite sua senha',
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Entrar',
                'class' => 'btn btn-primary w-100',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'email' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'EmailAddress',
                        'options' => [
                            'messages' => [
                                'emailAddressInvalidFormat' => 'Digite um e-mail válido',
                            ],
                        ],
                    ],
                ],
            ],
            'password' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 6,
                            'messages' => [
                                'stringLengthTooShort' => 'A senha deve ter no mínimo 6 caracteres',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
} 
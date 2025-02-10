<?php

declare(strict_types=1);

namespace Application\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class RegisterForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('register-form');

        $this->add([
            'name' => 'name',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Nome Completo',
            ],
            'attributes' => [
                'id' => 'name',
                'class' => 'form-control',
                'placeholder' => 'Digite seu nome completo',
                'required' => true,
            ],
        ]);

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
            'name' => 'phone',
            'type' => Element\Tel::class,
            'options' => [
                'label' => 'Telefone',
            ],
            'attributes' => [
                'id' => 'phone',
                'class' => 'form-control',
                'placeholder' => '(00) 00000-0000',
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
            'name' => 'confirm_password',
            'type' => Element\Password::class,
            'options' => [
                'label' => 'Confirme a Senha',
            ],
            'attributes' => [
                'id' => 'confirm_password',
                'class' => 'form-control',
                'placeholder' => 'Digite sua senha novamente',
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Cadastrar',
                'class' => 'btn btn-primary w-100',
            ],
        ]);
    }

    public function getInputFilterSpecification(): array
    {
        return [
            'name' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 3,
                            'max' => 100,
                            'messages' => [
                                'stringLengthTooShort' => 'O nome deve ter no mínimo 3 caracteres',
                                'stringLengthTooLong' => 'O nome deve ter no máximo 100 caracteres',
                            ],
                        ],
                    ],
                ],
            ],
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
            'phone' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                    [
                        'name' => 'PregReplace',
                        'options' => [
                            'pattern' => '/[^0-9]/',
                            'replacement' => '',
                        ],
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 10,
                            'max' => 11,
                            'messages' => [
                                'stringLengthTooShort' => 'O telefone deve ter no mínimo 10 dígitos',
                                'stringLengthTooLong' => 'O telefone deve ter no máximo 11 dígitos',
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
            'confirm_password' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    [
                        'name' => 'Identical',
                        'options' => [
                            'token' => 'password',
                            'messages' => [
                                'notSame' => 'As senhas não conferem',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
} 
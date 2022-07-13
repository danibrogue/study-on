<?php

namespace App\Form;


use App\DTO\CredentialsDTO;
use App\DTO\UserDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', EmailType::class, [
                'label' => 'E-mail',
                'required' => true,
                'constraints' => [
                    new Email([
                        'message' => "Некорректный email"
                    ]),
                    new Length([
                        'max' => 180
                    ])
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Пароли не совпадают',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Поле не может быть пустым'
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Пароль должен быть длиннее {{ limit }} символов',
                        'max' => 20,
                        'maxMessage' => 'Пароль должен быть короче {{ limit }} символов'
                    ])
                ],
                'first_name' => 'password',
                'second_name' => 'password_repeat',
                'first_options' => [
                    'label' => 'Пароль'
                ],
                'second_options' => [
                    'label' => 'Подтвердите пароль'
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Необходимо ваше соглашение с использованием персональных данных',
                    ]),
                ],
                'label' => 'Согласен с использованием моих персональных данных',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CredentialsDTO::class,
        ]);
    }
}

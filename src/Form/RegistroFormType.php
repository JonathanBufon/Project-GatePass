<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Define o formulário de registro, que atua como um DTO virtual.
 * Não está vinculado a nenhuma entidade (data_class = null) pois
 * seus dados serão distribuídos entre Usuario e Cliente.
 *
 * @author Jonathan Bufon
 */
class RegistroFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'seu-email@exemplo.com',
                    'class' => 'form-control' // Alinhado ao Bootstrap
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, insira um e-mail.']),
                ],
            ])
            ->add('nomeCompleto', TextType::class, [
                'label' => 'Nome Completo',
                'attr' => ['placeholder' => 'Seu nome completo', 'class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'O nome é obrigatório.']),
                ],
            ])
            ->add('cpf', TextType::class, [
                'label' => 'CPF',
                'attr' => ['placeholder' => '000.000.000-00', 'class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'O CPF é obrigatório.']),
                    new Length(['min' => 11, 'max' => 14, 'exactMessage' => 'O CPF deve ser válido.']),
                ],
            ])
            // Usamos RepeatedType para garantir a confirmação da senha
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false, // Não mapeia para a entidade (pois não há)
                'first_options' => [
                    'label' => 'Senha',
                    'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Confirmar Senha',
                    'attr' => ['class' => 'form-control', 'autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'Os campos de senha devem corresponder.',
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, insira uma senha.']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Sua senha deve ter pelo menos {{ limit }} caracteres.',
                        'max' => 4096, // Limite máximo do Symfony
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Li e aceito os termos de uso',
                'mapped' => false,
                'attr' => ['class' => 'form-check-input'],
                'constraints' => [
                    new IsTrue(['message' => 'Você deve aceitar nossos termos.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
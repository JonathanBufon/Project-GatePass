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
 * Define o formulário de registro do Vendedor (DTO virtual).
 * Não está vinculado a nenhuma entidade (data_class = null).
 * @author Jonathan Bufon
 */
class RegistroVendedorFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email de Acesso',
                'attr' => ['placeholder' => 'seu-email@exemplo.com', 'class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'Por favor, insira um e-mail.'])],
            ])
            ->add('nomeFantasia', TextType::class, [
                'label' => 'Nome Fantasia da Empresa',
                'attr' => ['placeholder' => 'Nome da sua produtora', 'class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'O nome é obrigatório.'])],
            ])
            ->add('cnpj', TextType::class, [
                'label' => 'CNPJ',
                'attr' => ['placeholder' => 'XX.XXX.XXX/0001-XX', 'class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'O CNPJ é obrigatório.']),
                    new Length(['min' => 14, 'max' => 18, 'exactMessage' => 'O CNPJ deve ser válido.']),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
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
                    new Length(['min' => 8, 'minMessage' => 'Sua senha deve ter pelo menos {{ limit }} caracteres.']),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'Li e aceito os termos de uso (Vendedor)',
                'mapped' => false,
                'attr' => ['class' => 'form-check-input'],
                'constraints' => [new IsTrue(['message' => 'Você deve aceitar nossos termos.'])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // SRP: É um DTO
        ]);
    }
}
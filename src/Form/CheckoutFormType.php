<?php

namespace App\Form;

use App\Dto\CheckoutDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * @author Jonathan Bufon
 * Define o formulário (DTO) para a página de Checkout.
 * Este formulário NÃO é vinculado a uma entidade (data_class = null).
 * Ele coleta os dados do comprador e a forma de pagamento.
 */
class CheckoutFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomeCompleto', TextType::class, [
                'label' => 'Nome Completo',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, informe seu nome.']),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'attr' => ['class' => 'form-control', 'placeholder' => 'voce@exemplo.com'],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, informe seu e-mail.']),
                    new Email(['message' => 'O e-mail "{{ value }}" não é válido.']),
                ],
            ])
            ->add('cpf', TextType::class, [
                'label' => 'CPF',
                'attr' => ['class' => 'form-control', 'placeholder' => '000.000.000-00'],
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, informe seu CPF.']),
                    new Regex([
                        'pattern' => '/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                        'message' => 'O CPF deve estar no formato 000.000.000-00.'
                    ]),
                ],
            ])
            ->add('formaPagamento', ChoiceType::class, [
                'label' => 'Forma de Pagamento',
                'choices' => [
                    'Cartão de Crédito (Mock)' => 'credit_card',
                    'Pix (Mock)' => 'pix',
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Por favor, selecione uma forma de pagamento.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CheckoutDto::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'checkout_token',
        ]);
    }
}
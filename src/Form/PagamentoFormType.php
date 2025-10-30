<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form (DTO) para coletar dados de pagamento.
 * Não está vinculado a nenhuma entidade (data_class = null).
 * @author Jonathan Bufon
 */
class PagamentoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Simulação dos campos do template 'Checkout'
        $builder
            ->add('paymentMethod', ChoiceType::class, [
                'choices'  => [
                    'Cartão de Crédito' => 'credit_card',
                    'PIX (Simulado)' => 'pix',
                ],
                'expanded' => true, // Renderiza como radio buttons
                'multiple' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('ccName', TextType::class, [
                'label' => 'Nome no Cartão',
                'attr' => ['class' => 'form-control'],
                'required' => false, // (Será obrigatório dinamicamente via JS)
            ])
            ->add('ccNumber', TextType::class, [
                'label' => 'Número do Cartão',
                'attr' => ['class' => 'form-control', 'placeholder' => 'xxxx xxxx xxxx xxxx'],
                'required' => false,
            ])
            ->add('ccExpiration', TextType::class, [
                'label' => 'Validade (MM/AA)',
                'attr' => ['class' => 'form-control', 'placeholder' => 'MM/AA'],
                'required' => false,
            ])
            ->add('ccCvv', TextType::class, [
                'label' => 'CVV',
                'attr' => ['class' => 'form-control', 'placeholder' => '123'],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Não mapeado a uma entidade
        ]);
    }
}
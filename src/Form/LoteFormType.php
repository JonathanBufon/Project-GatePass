<?php

namespace App\Form;

use App\Entity\Lote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Define o formulário (DTO) para um Lote individual.
 * Este formulário será usado como 'entry_type' no CollectionType do EventoFormType.
 */
class LoteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nome', TextType::class, [
                'label' => 'Nome do Lote',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Lote 1, Pista, Camarote']
            ])
            ->add('preco', MoneyType::class, [
                'label' => 'Preço (R$)',
                'currency' => 'BRL', // Define a moeda (Real Brasileiro)
                'attr' => ['class' => 'form-control']
            ])
            ->add('quantidadeTotal', IntegerType::class, [
                'label' => 'Qtd. Ingressos',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dataInicioVendas', DateTimeType::class, [
                'label' => 'Início das Vendas',
                'widget' => 'single_text', // HTML5 datetime-local
                'attr' => ['class' => 'form-control'],
                'required' => false // Campo opcional (nullable na entidade)
            ])
            ->add('dataFimVendas', DateTimeType::class, [
                'label' => 'Fim das Vendas',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'required' => false // Campo opcional (nullable na entidade)
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Vincula este formulário (DTO) diretamente à entidade Lote.
            'data_class' => Lote::class,
        ]);
    }
}
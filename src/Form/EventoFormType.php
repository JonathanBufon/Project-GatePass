<?php

namespace App\Form;

use App\Entity\Evento;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Define o formulário (DTO) para a criação e edição de Eventos.
 * Vinculado diretamente à entidade App\Entity\Evento.
 */
class EventoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nome', TextType::class, [
                'label' => 'Nome do Evento',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Show de Lançamento']
            ])
            ->add('local', TextType::class, [
                'label' => 'Local',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Arena Vênus']
            ])
            ->add('dataHoraInicio', DateTimeType::class, [
                'label' => 'Data e Hora de Início',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dataHoraFim', DateTimeType::class, [
                'label' => 'Data e Hora de Término',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('capacidadeTotal', IntegerType::class, [
                'label' => 'Capacidade Total',
                'attr' => ['class' => 'form-control']
            ])
            ->add('descricao', TextareaType::class, [
                'label' => 'Descrição (Opcional)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('urlBanner', UrlType::class, [
                'label' => 'URL do Banner (Opcional)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'https://...']
            ])

            ->add('lotes', CollectionType::class, [
                'entry_type' => LoteFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Lotes de Ingressos',
                'prototype_name' => '__lote_proto__',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evento::class,
        ]);
    }
}
<?php

namespace App\Form;

use App\Entity\Evento;
use App\Enum\TipoEstrutura;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
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
            ->add('tipoEstrutura', EnumType::class, [
                'class' => TipoEstrutura::class,
                'label' => 'Estrutura do Evento',
                'choice_label' => fn(TipoEstrutura $e) => match($e) { TipoEstrutura::PISTA => 'Pista', TipoEstrutura::ASSENTOS_NUMERADOS => 'Assentos Numerados' },
                'placeholder' => 'Selecione...',
                'required' => true,
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evento::class,
        ]);
    }
}
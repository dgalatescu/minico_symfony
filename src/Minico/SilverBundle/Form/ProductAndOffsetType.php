<?php
namespace Minico\SilverBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductAndOffsetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', 'text', array(
                'required'    => false,
                'empty_data'  => null
            ))
            ->add('offset', 'text', array(
                'required'    => false,
                'empty_data'  => null)
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    public function getName()
    {
        return 'productAndOffset';
    }
}

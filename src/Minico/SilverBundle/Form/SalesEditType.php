<?php

namespace Minico\SilverBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Minico\SilverBundle\Entity\ProductsRepository;
use Minico\SilverBundle\Entity\Sales;
use Minico\SilverBundle\Entity\Storage;
use Minico\SilverBundle\Service\StorageService;
use Proxies\__CG__\Minico\SilverBundle\Entity\Products;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
//use Genemu\Bundle\FormBundle\GenemuFormBundle;

class SalesEditType extends AbstractType
{
    /** @var  EntityManager */
    private $em;

    public function __construct(EntityManager $em, $products, Sales $sale){
        $this->em = $em;
        $this->products = $products;
        $this->sale = $sale;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'date',
                'genemu_jquerydate',
                array(
                    'widget' => 'single_text'
                )
            )
            ->add(
                'fromStorage',
                'entity',
                array(
                    'class' => 'MinicoSilverBundle:Storage',
                    'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('s')
                            ->where('s.sellingStorage=:sellingStorage')
                            ->setParameter('sellingStorage', 1)
                            ->orderBy('s.name', 'ASC');
                    },
                    'empty_value' => 'Choose an option',
                    'property' => 'name',
                    'read_only' => true,
                    'disabled' => true,
                )
            )
            ->add(
                'productId',
                'entity',
                array(
                    'class' => 'MinicoSilverBundle:Products',
                    'choices'     => $this->products,
                    'empty_value' => 'Choose an option',
                    'data' => $this->sale->getProductId()
                )
            )
            ->add('quantity');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Minico\SilverBundle\Entity\Sales'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'minico_silverbundle_sales';
    }
}

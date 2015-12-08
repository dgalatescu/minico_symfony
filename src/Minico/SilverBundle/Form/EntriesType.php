<?php

namespace Minico\SilverBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class EntriesType extends AbstractType
{

    private $em = null;
    private $productId = null;

    public function __construct(EntityManager $em, $productId=null)
    {
        $this->em = $em;
        $this->productId = $productId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $arr = array(
            'class' => 'MinicoSilverBundle:Products',
            'property'=>'productCode',
            'label' => 'Product',
            'required' => true,
        );
        if ($this->productId != null) {
            $arr ['data'] = $this->em->getReference(
                    "MinicoSilverBundle:Products",
                    $this->productId
                    );
        }

        $builder
            ->add(
                'productId',
                'entity', 
                $arr
            )
            ->add(
                'storage',
                'entity',
                array(
                    'class' => 'MinicoSilverBundle:Storage',
                    'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('s')
                            ->where('s.mainStorage=:mainStorage')
                            ->setParameter('mainStorage', 1)
                            ->orderBy('s.name', 'ASC');
                    },
                    'data' => null
                )
            )
            ->add('quantity')
            ->add('save', 'submit');
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Minico\SilverBundle\Entity\Entries'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'minico_silverbundle_entries';
    }
}

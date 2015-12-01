<?php
namespace Minico\SilverBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Minico\SilverBundle\Entity\Entries;
use Minico\SilverBundle\Entity\EntriesRepository;
use Minico\SilverBundle\Entity\Products;
use Minico\SilverBundle\Entity\Sales;
use Minico\SilverBundle\Entity\SalesRepository;
use Minico\SilverBundle\Entity\Storage;
use Minico\SilverBundle\Entity\Transfer;
use Minico\SilverBundle\Entity\TransferRepository;
use Minico\SilverBundle\Entity\Withdrawls;
use Minico\SilverBundle\Entity\WithdrawlsRepository;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class TransferService {
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->container;
    }


    /**
     * @param Storage $storage
     * @param Products $product
     * @return int
     */
    public function getTransferMaxQty(Storage $storage, Products $product)
    {
        $em = $this->doctrine->getManager();

        /** @var TransferRepository $transferRepository */
        $transferRepository = $em->getRepository('MinicoSilverBundle:Transfer');
        $qtyIn = 0;
        $qtyOut = 0;
        $mainStorageQty = 0;
        $soldQty = 0;
        if ($storage->getMainStorage()) {
            /** @var EntriesRepository $entriesRepo */
            $entriesRepo = $em->getRepository('MinicoSilverBundle:Entries');

            /** @var Entries[] $mainStorageQtyArray */
            $mainStorageQtyArray = $entriesRepo->findBy(
                array(
                    'productId' => $product,
                    'storage' => $storage
                )
            );
            /** @var Entries $curr */
            foreach ($mainStorageQtyArray as $curr) {
                $mainStorageQty += $curr->getQuantity();
            }

            /** @var WithdrawlsRepository $withdrawalRepo */
            $withdrawalRepo = $em->getRepository('MinicoSilverBundle:Withdrawls');

            /** @var Withdrawls[] $mainStorageQtyArray */
            $mainStorageQtyArray = $withdrawalRepo->findBy(
                array(
                    'productId' => $product,
                    'storage' => $storage
                )
            );
            /** @var Withdrawls $curr */
            foreach ($mainStorageQtyArray as $curr) {
                $mainStorageQty -= $curr->getQuantity();
            }
        }

        if ($storage->getSellingStorage()) {
            /** @var SalesRepository $salesRepo */
            $salesRepo = $em->getRepository('MinicoSilverBundle:Sales');

            /** @var Sales[] $salesQtyArray */
            $salesQtyArray = $salesRepo->findBy(
                array(
                    'productId' => $product,
                    'fromStorage' => $storage
                )
            );
            /** @var Sales $curr */
            foreach ($salesQtyArray as $curr) {
                $soldQty += $curr->getQuantity();
            }
        }

        $qtyOutOfStorageArray = $transferRepository->findBy(
            array(
                'product' => $product,
                'fromStorage' => $storage
            )
        );

        /** @var Transfer $curr */
        foreach ($qtyOutOfStorageArray as $curr) {
            $qtyOut += $curr->getQty();
        }


        $qtyIntoStorageArray = $transferRepository->findBy(
            array(
                'product' => $product,
                'toStorage' => $storage
            )
        );

        /** @var Transfer $curr */
        foreach ($qtyIntoStorageArray as $curr) {
            $qtyIn += $curr->getQty();
        }

        $maxQty = $mainStorageQty + $qtyIn - $qtyOut - $soldQty;

        return $maxQty;
    }
}

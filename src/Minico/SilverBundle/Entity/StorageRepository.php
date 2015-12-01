<?php

namespace Minico\SilverBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * StorageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StorageRepository extends EntityRepository
{

    /**
     * @param Storage $storage
     * @return array
     */
    public function getSumEntriesForMainStorage(Storage $storage, $product = null)
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('p.id as productId, sum(e.quantity) as result')
            ->innerJoin('s.entries', 'e')
            ->innerJoin('e.productId', 'p')
            ->where('s.id = :storage')
            ->setParameter('storage', $storage->getId())
            ->groupBy('e.productId')
            ->orderBy('p.category');

        if (!empty($product)) {
            $query
                ->andWhere('e.productId = :product')
                ->setParameter('product', $product);

            return $query->getQuery()->getOneOrNullResult();
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Storage $storage
     * @return array
     */
    public function getSumWithdrawsForMainStorage(Storage $storage, $product = null)
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('p.id as productId, sum(w.quantity) as result')
            ->innerJoin('s.withdraws', 'w')
            ->innerJoin('w.productId', 'p')
            ->where('s.id = :storage')
            ->setParameter('storage', $storage->getId())
            ->groupBy('w.productId');

        if (!empty($product)) {
            $query
                ->andWhere('w.productId = :product')
                ->setParameter('product', $product);

            return $query->getQuery()->getOneOrNullResult();
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Storage $storage
     * @return array
     */
    public function getSumTransferFromForMainStorage(Storage $storage, $product = null)
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('p.id as productId, sum(f.qty) as result')
            ->innerJoin('s.transfersFrom', 'f')
            ->innerJoin('f.product', 'p')
            ->where('s.id = :storage')
            ->setParameter('storage', $storage->getId())
            ->groupBy('f.product')
            ->orderBy('p.category');

        if (!empty($product)) {
            $query
                ->andWhere('f.product = :product')
                ->setParameter('product', $product);

            return $query->getQuery()->getOneOrNullResult();
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Storage $storage
     * @return array
     */
    public function getSumTransferToForMainStorage(Storage $storage, $product = null)
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('p.id as productId, sum(tt.qty) as result')
            ->innerJoin('s.transfersTo', 'tt')
            ->innerJoin('tt.product', 'p')
            ->where('s.id = :storage')
            ->setParameter('storage', $storage->getId())
            ->groupBy('tt.product')
            ->orderBy('p.category', 'ASC');

        if (!empty($product)) {
            $query
                ->andWhere('tt.product = :product')
                ->setParameter('product', $product);

            return $query->getQuery()->getOneOrNullResult();
        }

        return $query
            ->getQuery()
            ->getResult();
    }



    public function getProductsForSellingStorage(Storage $storage, $product = null)
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('p.id as productId, sum(sa.quantity) as result')
            ->innerJoin('s.salesFrom', 'sa')
            ->innerJoin('sa.productId', 'p')
            ->where('s.id = :storage')
            ->setParameter('storage', $storage->getId())
            ->groupBy('sa.productId');

        if (!empty($product)) {
            $query
                ->andWhere('sa.productId = :product')
                ->setParameter('product', $product);

            return $query->getQuery()->getOneOrNullResult();
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    public function getProductsOfMainStorage(Storage $storage)
    {
        if ($storage->getMainStorage() == Storage::ID_MAIN_STORAGE) {
            $sql = "select e1.pid as productId, (e1.s - IF(w1.s is null,0,w1.s) - IF(t1.s is null,0,t1.s) + IF(st1.s IS NULL,0,st1.s)) as result from
                        (select e.productId as pid, sum(e.quantity) as s from entries e where e.storageId = ".$storage->getId()." group by e.productId) as e1
                        left join (select w.productId as pid, sum(w.quantity) as s from withdrawls w where w.storageId = ".$storage->getId()." group by w.productId) as w1
                            ON e1.pid = w1.pid
                        left join (select t.productId as pid, sum(t.quantity) as s from transfer t where t.storageFrom = ".$storage->getId()." group by t.productId) as t1
                            ON t1.pid = e1.pid
                        LEFT JOIN (SELECT t.productId AS pid, SUM(t.quantity) AS s FROM transfer t WHERE t.storageTo = ".$storage->getId()." GROUP BY t.productId) AS st1
                            ON st1.pid = e1.pid";
        } else {
            $sql = "
                select st1.pid as productId, (st1.s - IF(sf1.s is null,0,sf1.s) - IF(sa1.s is null,0,sa1.s)) as result from
                    (select st.productId as pid, sum(st.quantity) as s from transfer st where st.storageTo = ".$storage->getId()." group by st.productId) as st1
                    left join (
                        select t.productId as pid, sum(t.quantity) as s from transfer t where t.storageFrom = ".$storage->getId()." group by t.productId) as sf1
                            ON st1.pid = sf1.pid
                    left join (
                        select sa.productId as pid, sum(sa.quantity) as s from sales sa where sa.storageFrom = ".$storage->getId()." group by sa.productId) as sa1
                            ON st1.pid = sa1.pid";
        }

        $stmt = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql);

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
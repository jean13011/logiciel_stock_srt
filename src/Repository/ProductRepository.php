<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Product
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function mofifyReference(int $id, string $ref)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "UPDATE `product` 
                SET reference = :reference
                WHERE id = :id   
                ";
        
        $query = $conn->prepare($sql);
        $exec = $query->execute([
            "reference" => $ref,
            "id" => $id
        ]);

        return $exec;
    }

    public function modifyQuantity(int $quantity, int $id)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "UPDATE `product` SET quantity = :quantity 
                WHERE id = :id
                ";

        $query = $conn->prepare($sql);
        $exec = $query->execute([
            "quantity" => $quantity,
            "id" => $id

        ]);
    }
}

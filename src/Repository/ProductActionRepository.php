<?php

namespace App\Repository;

use App\Entity\ProductAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductAction[]    findAll()
 * @method ProductAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductAction::class);
    }

    // /**
    //  * @return ProductAction[] Returns an array of ProductAction objects
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
    public function findOneBySomeField($value): ?ProductAction
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findActions()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT * FROM  `product_action` 
                WHERE 1
                ";
        
        $query = $conn->prepare($sql);
        $query->execute();
        $result = $query->fetchAll();
        return $result;


    }

    public function actionForModifiedReference(int $id, string $ref)
    {
        $conn = $this->getEntityManager()->getConnection();
    
        $sql = "INSERT INTO `product_action` 
                (idProduct, newReference, modificationDate)
                VALUES (:id, :ref, NOW())
                ";
    
        $query = $conn->prepare($sql);
        $exec = $query->execute([
            "ref" => $ref,
            "id" => $id
        ]);
    
        return $exec;
    }

    public function actionForModifiedName(int $id, string $name)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "INSERT INTO `product_action` 
                (idProduct, newName, modificationDate)
                VALUES (:id, :name, NOW())
                ";

        $query = $conn->prepare($sql);
        $exec = $query->execute([
            "name" => $name,
            "id" => $id
        ]);

        return $exec;
    }


    public function actionForModifiedEmplacement(int $id, string $emplacement)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "INSERT INTO `product_action` 
                (idProduct, newEmplacement, modificationDate)
                VALUES (:id, :emplacement, NOW())
                ";

        $query = $conn->prepare($sql);
        $exec = $query->execute([
            "emplacement" => $emplacement,
            "id" => $id
        ]);

        return $exec;
    }

    public function actionForModifiedQuantity(int $id, int $quantity)
    {
        $conn = $this->getEntityManager()->getConnection();
    
        $sql = "INSERT INTO `product_action` 
                (idProduct, newReference, modificationDate)
                VALUES (:id, :ref, NOW())
                ";
    
        $query = $conn->prepare($sql);
        $exec = $query->execute([
            "ref" => $ref,
            "id" => $id
        ]);
    
        return $exec;
    }
}

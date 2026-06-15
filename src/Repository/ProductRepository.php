<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    
    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Фильтрация 
     */
    public function searchByQuery(string $query): mixed
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :query')
            ->orWhere('p.uniqueCode LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.id', 'DESC')
            ->getQuery();
    }

    public function findByFilters(?string $categoryCode, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->orderBy('p.id', 'DESC');

        if ($categoryCode) {
            $qb->andWhere('c.code = :categoryCode')
               ->setParameter('categoryCode', $categoryCode);
        }

        $products = $qb->getQuery()->getResult();

        if (empty($filters)) {
            return array_values($products);
        }

        // ТВОЙ РОДНОЙ БЛОК ФИЛЬТРАЦИИ БЕЗ ИЗМЕНЕНИЙ:
        $filteredProducts = array_filter($products, function(Product $product) use ($filters) {
            $prodAttrs = $product->getAttributes() ?? [];
            
            foreach ($filters as $key => $value) {
                if (empty($value) || $key === 'page') {
                    continue;
                }
                
                if (isset($prodAttrs[$key]) && $prodAttrs[$key] === $value) {
                    continue;
                }
                
                $found = false;
                foreach ($prodAttrs as $attrValue) {
                    if (is_string($attrValue) && str_contains($attrValue, $value)) {
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    return false;
                }
            }
            return true;
        });
    return array_values($filteredProducts);
    }
}
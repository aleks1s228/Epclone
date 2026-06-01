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
     * Фильтрация товаров по категории и JSON-аттрибутам
     */
public function findByFilters(?string $categoryCode, array $filters = []): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c');

        if ($categoryCode) {
            $qb->andWhere('c.code = :categoryCode')
               ->setParameter('categoryCode', $categoryCode);
        }

        $products = $qb->getQuery()->getResult();

        if (empty($filters)) {
            return $products;
        }

        return array_filter($products, function(Product $product) use ($filters) {
            $prodAttrs = $product->getAttributes() ?? [];
            
            foreach ($filters as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                
                // Проверяем: если это ассоциативный массив (ключ => значение)
                if (isset($prodAttrs[$key]) && $prodAttrs[$key] === $value) {
                    continue;
                }
                
                // Если EasyAdmin сохранил просто как список строк (например, ["AM5", "120W"])
                // Ищем вхождение строки во всем массиве атрибутов
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
    }
}
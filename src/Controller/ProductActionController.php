<?php

namespace App\Controller;

use App\Entity\ProductAction;
use App\Repository\ProductActionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductActionController extends AbstractController
{
    /**
     * @Route("/product/action", name="product_action")
     * 
     * @param object ProductActionRepository $action the action repository
     * @param object PaginatorInterface $paginator a package to paginate actions every 5 actions
     * @param object Request $req for the paginator
     * 
     * @return object Response for src/template/action/action.html.twig
     * 
     * it display all actions made by the user on a product
     */
    public function action(ProductActionRepository $action, PaginatorInterface $paginator, Request $req): Response
    {
        $products = $paginator->paginate($action->findActions(), $req->query->getInt("page", 1), 10);

        return $this->render('product_action/action.html.twig', [
            'products' => $products
        ]);
    }

}

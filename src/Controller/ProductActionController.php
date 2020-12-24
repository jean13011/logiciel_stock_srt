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
     */
    public function action(ProductActionRepository $action, PaginatorInterface $paginator, Request $req): Response
    {
        $products = $paginator->paginate($action->findActions(), $req->query->getInt("page", 1), 5);

        return $this->render('product_action/action.html.twig', [
            'products' => $products
        ]);
    }

}

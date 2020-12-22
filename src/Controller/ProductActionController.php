<?php

namespace App\Controller;

use App\Entity\ProductAction;
use App\Repository\ProductActionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductActionController extends AbstractController
{
    /**
     * @Route("/product/action", name="product_action")
     */
    public function action(ProductActionRepository $action): Response
    {
        return $this->render('product_action/action.html.twig', [
            'products' => $action->findActions()
        ]);
    }
}

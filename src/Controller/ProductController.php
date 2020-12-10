<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Doctrine\ORM\EntityManagerInterface;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product")
     */
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    /**
     * @Route("/newProduct", name="product_type")
     * 
     * if the form is valid en submitted we send all the datas in the DB, we save the image and generate a barcode wich is the reference
     * 
     * @return view
     */
    public function addNewProduct(Request $req, EntityManagerInterface $manager)
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $code = "";
        
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($product);
            $manager->flush();
            
            if(isset($_FILES["product"])) 
            {
                $name_img = $_FILES["product"]["name"]["name_img"];
                $path_img = $_FILES["product"]["tmp_name"]["name_img"];
                $image = copy($path_img ,dirname(dirname(__DIR__)) . "/public/img/" . $name_img);
            }

            $generator =  new BarcodeGeneratorPNG();
            $code = base64_encode($generator->getBarcode($product->getReference(), $generator::TYPE_CODE_128));
        }
        
        

        return $this->render("product/newProduct.html.twig", 
        [
            "form" => $form->createView(),
            "code" => $code
            
        ]);
    }

    /**
     * @Route("/searchProduct", name="product_search")
     */
    public function displayProduct(ProductRepository $repo)
    {
        return $this->render('product/searchProduct.html.twig', [
            'products' => $repo->findAll()
        ]);
    }
}

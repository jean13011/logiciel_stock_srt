<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\SearchType;
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
     * @param Object Request to handle the form
     * @param Object EntityManagerInterface $manager to persist all datas and flush them in the DB
     * 
     * @return Response
     */
    public function addNewProduct(Request $req, EntityManagerInterface $manager):Response
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
     * 
     * it display all products from the db 
     * @param Object ProductRepository $repo to find all the products from the repository
     * 
     * @return Response 
     */
    public function displayProduct(ProductRepository $repo): Response
    {

        return $this->render('product/searchProduct.html.twig', [
            'products' => $repo->findAll()
        ]);
    } 

    /**
     * @Route("/yourProduct", name="product_search_reference")
     * 
     * @param Object ProductRepository $repo to find the product by the reference entered into the form
     * 
     * @return Response
     */
    public function searchByReference(ProductRepository $repo): Response
    {
        if(isset($_GET)) 
        {
            $find = $repo->findOneBy([
                "reference" => $_GET["product"]["reference"]
            ]);
            return $this->render('product/searchOneProduct.html.twig', [
                'products' => $find
            ]);
        }
        return new Response("produit non trouvé"); 
    }

    /**
     * @Route("/ModifyProduct/{id}", name="product_modify")
     * @param Object ProductRepository for doctrine 
     * @param Int $id from the stock
     * 
     * @return Response
     */
    public function modifyProduct( ProductRepository $repo, int $id):Response
    {
        if(isset($id)) 
        {
            $find = $repo->find($id);
            return $this->render('product/updateProduct.html.twig', [
                'products' => $find
            ]);
        }
    }
}

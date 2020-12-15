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

    ////////////////////////create

    /**
     * @Route("/newProduct", name="product_type")
     * 
     * if the form is valid en submitted we send all the datas in the DB, we save the image and generate a barcode wich is the reference
     * @param object Product product  Request to handle the form
     * @param object Product product  EntityManagerInterface $manager to persist all datas and flush them in the DB
     * 
     * @return Response for src/template/product/newProduct.html.twig
     */
    public function add(Request $req, EntityManagerInterface $manager):Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $code = "";
        
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($product);
            $manager->flush();

            $generator =  new BarcodeGeneratorPNG();
            $code = base64_encode($generator->getBarcode($product->getReference(), $generator::TYPE_CODE_128));
        }

        return $this->render("product/newProduct.html.twig", 
        [
            "form" => $form->createView(),
            "code" => $code 
        ]);
    }
    /////////////////////////// read
    /**
     * @Route("/searchProduct", name="product_search")
     * 
     * it display all products from the db 
     * @param object Product product  ProductRepository $repo to find all the products from the repository
     * 
     * @return object Response for src/template/product/searchProduct.html.twig
     */
    public function display(ProductRepository $repo): Response
    {

        $orderBy = "order by name";

        return $this->render('product/searchProduct.html.twig', [
            'products' => $repo->findBy([], ["name" => "asc"] )
        ]);
    } 

    /**
     * @Route("/yourProduct", name="product_search_reference")
     * 
     * search  product by the reference in the code bar or manualy entered
     * @param object Product product  ProductRepository $repo to find the product by the reference entered into the form
     * 
     * @return Response for src/template/product/searchOneProduct.html.twig
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
     * @Route("/searchByRack", name="product_search_by_rack")
     * 
     * search the product by the  rack's emplacement ASC
     * 
     * @param object ProductRepository $repo to find all products by emplacement
     * 
     * @return object Response
     */
    public function searchByRack(ProductRepository $repo): Response
    {
    
        $find = $repo->findBy([], ["emplacement" => "asc"]);

        return $this->render('product/searchProduct.html.twig', [
            'products' => $find
        ]);
        
    }

    ///////////////////////////////update
    /**
     * @Route("/modifyProduct/{id}", name="product_modify")
     * 
     * set an interface with multiple buttons for update or delete somes product's infos but no modification for the moment with this
     * @param object Product product  ProductRepository for doctrine 
     * @param Int $id from the stock
     * 
     * @return Response for src/template/product/updateProduct.html.twig
     */
    public function modifyInterface(ProductRepository $repo, int $id):Response
    {
        if(isset($id)) 
        {
            $find = $repo->find($id);
            return $this->render('product/updateProduct.html.twig', [
                'products' => $find
            ]);
        }
    }

    /**
     * @Route("/modifyReference/{id}", name="product_modify_reference")
     * 
     */
    public function modifyReference(Request $req, int $id, ProductRepository $prod)
    {
        $product = new Product;
        
        $form = $this->createForm(SearchType::class, $product);

        $code = "";

        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid())
        {
            $ref = $product->getReference();
            $prod->mofifyReference($id, $ref);

            $generator =  new BarcodeGeneratorPNG();
            $code = base64_encode($generator->getBarcode($product->getReference(), $generator::TYPE_CODE_128));
        }

        return $this->render("product/modifyReference.html.twig", 
        [
            "form" => $form->createView(),
            "code" => $code 
        ]);
    }

    /**
     * @Route("/modifyQuantity", name="product_modify_quantity")
     */
    public function modifyQuantity(Request $req, ProductRepository $prod)
    {
        $number = $req->request->get("number");
        $id = $req->request->get("id");

        if(isset($number) && isset($id))
        {
            $prod->modifyQuantity($number, $id);
            $result = $prod->findOneBy(["id" => $id]);
            return $this->json(["reponse" => "Quantité mise à jour", "resultat" => $result],  200);

        }
        else 
        {

            return $this->json(["error" => "aucun numero a été envoyé"], 200);
        }
    }

    ///////////////////////// delete
    /**
     * @Route("/delete/{id}", name="product_delete")
     * 
     * delete the entire product no matter the stock so be carefull
     * @param object EntityManagerInterface $manager to the access to doctrine
     * @param object Product $product my Product object
     * 
     * @return object Response for src/template/product/searchProduct.html.twig
     */
    public function delete(EntityManagerInterface $manager, Product $product): Response
    {
        $manager->remove($product);
        $manager->flush();

        return $this->redirectToRoute("product_search", ["Response" => "produit suppimé"]);
    }
}

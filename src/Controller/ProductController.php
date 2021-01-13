<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Product;
use App\Form\SearchType;
use App\Form\ProductType;
use App\Form\ConnexionType;
use Symfony\Component\Ldap\Ldap;
use App\Form\ChangeProductNameType;
use App\Repository\ProductRepository;
use Symfony\Component\Form\FormError;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ChangeProductEmplacementType;
use App\Repository\ProductActionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{

    /**
     * @Route("/" , name="index")
     */
    public function index():Response
    {
        return $this->redirectToRoute("user_login");
    }
    
    //////////////////////// CREATE 

    /**
     * @Route("/newProduct", name="product_type")
     * 
     * if the form is valid en submitted we send all the datas in the DB, we save the image and generate a barcode wich is the reference
     * @param object Product product  Request to handle the form
     * @param object Product product  EntityManagerInterface $manager to persist all datas and flush them in the DB
     * 
     * @return object Response for src/template/product/newProduct.html.twig
     */
    public function addNew(Request $req, EntityManagerInterface $manager): Response
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

    public function createNewPurchaseOrder(Request $req, EntityManagerInterface $manager): Response
    {
        //je dois ajouter un grand formulaire du meme style que le stock mais y ajouter un champ "à soustraire" et soustraire le chiffre entrée au celui de la quantité 
        //et pour chaques produit où la soustraction doit etre faites il faudra créer un bon de commande avec les données(nom, ref , qte(commanées, reçus))
        //si la soustraction est egale ou inferieur a 0 alors on return

    }
    
    /////////////////////////// READ
    /**
     * @Route("/searchProduct", name="product_search")
     * 
     * displays all entered products and paginates all 5 products
     * @param object Product product  ProductRepository $repo to find all the products from the repository
     * 
     * @return object Response for src/template/product/searchProduct.html.twig
     */
    public function display(ProductRepository $repo, Request $req, PaginatorInterface $paginator): Response
    {

        $products = $paginator->paginate($repo->findBy([], ["name" => "asc"] ), $req->query->getInt("page", 1), 20);

        return $this->render('product/searchProduct.html.twig', [
            'products' => $products
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
    public function searchByRack(ProductRepository $repo, PaginatorInterface $paginator,Request $req): Response
    {
    
        $products = $paginator->paginate($repo->findBy([], ["emplacement" => "asc"]), $req->query->getInt("page", 1), 20);

        return $this->render('product/searchProduct.html.twig', [
            'products' => $products
        ]);
        
    }

    ///////////////////////////////UPDATE

    /**
     * @Route("/modifyProduct/{id}", name="product_modify")
     * 
     * set an interface with multiple buttons for update or delete somes product's infos but no modification for the moment with this
     * @param object Product product  ProductRepository for doctrine 
     * @param Int $id from the stock
     * 
     * @return Response for src/template/product/updateProduct.html.twig
     */
    public function modifyInterface(ProductRepository $repo, int $id): Response
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
     * this will modify the reference of the product selected and give a new barecode
     * @param object Request $req for the request 
     * @param object int $id , the product's id 
     * @param object ProductRepository $prod to modify the reference by a method in the repository
     * @param object ProductActionRepository $action to add the modification and the time in the history
     * 
     * @return object Response for src/template/product/modifyReference.html.twig
     * 
     */
    public function modifyReference(Request $req, int $id, ProductRepository $prod,  ProductActionRepository $action): Response
    {
        $product = new Product;
        
        $form = $this->createForm(SearchType::class, $product);

        $code = "";

        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid())
        {
            $ref = $product->getReference();
            $prod->mofifyReference($id, $ref);
            $action->actionForModifiedReference($id, $ref);

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
     * @Route("/modifyName/{id}", name="product_modify_name")
     * 
     *  this will modify the name of the product selected and give a new barecode
     * @param object Request $req for the request 
     * @param object int $id , the product's id 
     * @param object ProductRepository $prod to modify the name by a method in the repository
     * @param object ProductActionRepository $action to add the modification and the time in the history
     * 
     * @return object Response for src/template/product/modifyName.html.twig
     */
    public function modifyName(Request $req, int $id, ProductRepository $prod, ProductActionRepository $action): Response
    {
        $product = new Product;
        
        $form = $this->createForm(ChangeProductNameType::class, $product);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid())
        {
            $name = $product->getName();
            $prod->mofifyName($id, $name);
            $action->actionForModifiedName($id, $name);
            $this->addFlash("success", "Nom du produit modifié !");
        }

        return $this->render("product/modifyName.html.twig", 
        [
            "form" => $form->createView(),
        ]);
    }

     /**
     * @Route("/modifyEmplacement/{id}", name="product_modify_emplacement")
     * 
     *  this will modify the emplacement of the product selected and give a new barecode
     * @param object Request $req for the request 
     * @param object int $id , the product's id 
     * @param object ProductRepository $prod to modify the emplacement by a method in the repository
     * @param object ProductActionRepository $action to add the modification and the time in the history
     * 
     * @return object Response for src/template/product/modifyEmplacement.html.twig
     */
    public function modifyEmplacement(Request $req, int $id, ProductRepository $prod, ProductActionRepository $action): Response
    {
        $product = new Product;
        
        $form = $this->createForm(ChangeProductEmplacementType::class, $product);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid())
        {
            $emplacement = $product->getEmplacement();
            $prod->mofifyEmplacement($id, $emplacement);
            $action->actionForModifiedEmplacement($id, $emplacement);
            $this->addFlash("success", "Emplacement modifié !");
        }

        return $this->render("product/modifyEmplacement.html.twig", 
        [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @Route("/modifyQuantity", name="product_modify_quantity")
     * 
     * @param object Request $req to get the number set and the id to modify with ajax in the DB
     * @param object ProductRepository $prod to find the product by the id
     * 
     */
    public function modifyQuantity(Request $req, ProductRepository $prod, ProductActionRepository $action)
    {
        $number = $req->request->get("number");
        $id = $req->request->get("id");

        if(isset($number) && isset($id))
        {
            $prod->modifyQuantity($number, $id);
            $action->actionForModifiedQuantity($id, $number);
            $result = $prod->findOneBy(["id" => $id]);
            return $this->json(["reponse" => "Quantité mise à jour", "resultat" => $result],  200);

        } else 
        {
            return $this->json(["error" => "aucun numero a été envoyé"], 200);
        }
    }

    ///////////////////////// DELETE

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

        return $this->redirectToRoute("product_search");
    }
}

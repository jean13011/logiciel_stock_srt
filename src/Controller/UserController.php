<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ConnexionType;
use Symfony\Component\Ldap\Ldap;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="user_login")
     */
    public function connexionInLpad(Request $req, Ldap $ldap, EntityManagerInterface $manager, UserRepository $repo, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $form = $this->createForm(ConnexionType::class, $user);

        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid())
        {
            try 
            {
                $request = $req->request->get("connexion");
                $uid= $request["user_name"];
                $pass = $request["password"];
                $password = $pass;

                $conn = $ldap->getEntryManager();
                $dn = "uid=".$uid.",ou=users,dc=yunohost,dc=org";
        
                Ldap::create('ext_ldap', [
                    'host' => 'login.am-conseil.eu',
                    "port" => "389" ,
                ]);
                
                $ldap->bind($dn, $password);
                $query = $ldap->query('dc=yunohost,dc=org', '(&(objectClass=inetOrgPerson)(uid='.$uid.'))' );
                $results = $query->execute()->toArray();
                dd($results);
            } 
            
            catch (\Throwable $th) 
            {
                $form->addError(new FormError('Veuillez vérifier vos informations de connection!'));
            }
            
            if( isset($results) && is_array($results))
            {
                $find = $repo->findOneBy([
                    "user_name" => $uid
                ]);
                
                if ($find == true) 
                {
                    return $this->redirectToRoute("product_type");
                } 
                
                else 
                {
                    $hash = $encoder->encodePassword($user, $user->getPassword());
                    $user->setPassword($hash);
                    $user->setRole($user->getRoles());
                    $manager->persist($user);
                    $manager->flush();
                }
            }
        }

        return $this->render('user/login.html.twig', [
            'controller_name' => 'ProductController',
            "form" => $form->createView()
        ]);
    }

}

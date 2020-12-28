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
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route("/firstLogin", name="user_first_login")
     * 
     * @param object Request $req to analyse the request send by the new user
     * @param object Ldap $ldap a package known to add employees to an internal company directory
     * @param object EntityManagerInterface $manager to send to the application database the new user entered thanks to his ldap credential
     * @param object UserRepository $repo to find if a user i already defined in my DB
     * @param object UserPasswordEncoderInterface $encoder package to hash the password in bcrypt 
     * 
     * @return object Response for src/template/user/firstLogin.html.twig
     * 
     * this interface is given outside the application, it concerns the unregistered member of the company. we etablishing the connection with ldap's server and if the credentials are valid we create a knew user in DB to authentifate him with symfony
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
                
            } catch (\Throwable $th) 
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
                    return $this->redirectToRoute("user_login");
                    
                } else 
                {
                    $user->getRoles();
                    $hash = $encoder->encodePassword($user, $user->getPassword());
                    $user->setPassword($hash);
                    $user->setRole($user->getRoles());
                    $manager->persist($user);
                    $manager->flush();
                    return $this->redirectToRoute("user_login");
                }
            }
        }

        return $this->render('user/firstLogin.html.twig', [
            'controller_name' => 'ProductController',
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/login", name="user_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('product_type');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername, 'error' => $error
            ]);
    }

    /**
     * @Route("/logout", name="user_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute("user_login");
    }


}

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
     * this interface is given outside the application, it concerns the unregistered member of the company. we etablishing the connection with ldap's server and 
     * if the credentials are valid we create a new user in DB to authentifate him with symfony
     */
    public function connexionInLpad(Request $req, Ldap $ldap, EntityManagerInterface $manager, UserRepository $repo, UserPasswordEncoderInterface $encoder): Response
    {
        // instanciating User class
        $user = new User();

        // binding the form with User class
        $form = $this->createForm(ConnexionType::class, $user);

        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid())
        {
            try 
            {
                //retrieving the request object and its user_name and password fields, puting them in variables
                $request = $req->request->get("connexion");
                $uid= $request["user_name"];
                $pass = $request["password"];
                $password = $pass;

                //by following the indications of the LDAP API on symfony, i set up the connection to the directory
                $conn = $ldap->getEntryManager();
                $dn = "uid=".$uid.",ou=users,dc=yunohost,dc=org";
                Ldap::create('ext_ldap', [
                    'host' => 'login.am-conseil.eu',
                    "port" => "389" ,
                ]);

                $ldap->bind($dn, $password);
                $query = $ldap->query('dc=yunohost,dc=org', '(&(objectClass=inetOrgPerson)(uid='.$uid.'))' );
                //if there is a result i place it in array
                $results = $query->execute()->toArray();
                
            } catch (\Throwable $th) 
            {
                //if any problem is encountered in the try an error will be send to the twig's form
                $form->addError(new FormError('Veuillez vérifier vos informations de connexion!'));
            }
            
            if( isset($results) && is_array($results))
            {
                $find = $repo->findOneBy([
                    "user_name" => $uid
                ]);
                
                //if an user is found by his username the query will return true and signficate that the user is already in the DB
                //so we redirect him to the main connection interface
                if ($find == true) 
                {
                    return $this->redirectToRoute("user_login");
                    
                } else 
                {
                    //if all credentials are valids and the user is not on the DB, we have to make sur to hash the password for more security with method in $encoder
                    //we persist, flush it to the DB and redirect immediatly on the main connction interface
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
            //return $form wich is our variable for ConnexionType
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/login", name="user_login")
     * @param object AuthenticationUtils $authenticationUtils wich is a part of security component, it provide the authentification
     * @return object Response for template/security/login.html.twig
     * 
     * method pre-writed by symfony, if connection is succesfull symfony will redirect in product_type route
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
            ]
        );
    }

    /**
     * @Route("/logout", name="user_logout")
     * @return object Response for path call user_login
     * 
     * if user press "déconnexion" it call this controller and it will disconnect the user et redirect him to the login form
     */
    public function logout(): Response
    {
        return $this->redirectToRoute("user_login");
    }

    /**
     * @Route("/legalNotice", name="user_notice")
     */
    public function notice(){
        return $this->render('security/notice.html.twig');
    }

}

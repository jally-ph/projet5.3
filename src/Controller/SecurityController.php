<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\SearchType;
use App\Form\RegistrationType;
use Symfony\Component\Form\FormView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        //barre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(Request $request){

        //barre de recherche
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }

        return $this->render('security/login.html.twig', [
            'formSearch' => $searchForm->createView()
        ]);
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout() {}

    /**
     * @Route("/search/{search}", name="search")
     */
    public function search($search, Request $request)
    {
        $searchForm = $this->createForm(SearchType::class, null);

        if($request->isMethod("POST")){
            $searchForm->handleRequest($request);

            if($searchForm->isSubmitted() && $searchForm->isValid()){
                return $this->redirectToRoute("search", [
                    'search' => $searchForm["search"]->getData()
                ]);
            }
        }
        dd($search);
    }
}

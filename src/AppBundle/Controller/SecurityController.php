<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Role;
use AppBundle\Form\UserType;

class SecurityController extends BaseController
{
    /**
     * @Route(path="/login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $query = $request->query->getAlnum('q');
        $className = $request->query->getAlnum('type');
        $templateData['title'] = 'Sign-in';
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_security_register');
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
        $templateData['searchForm'] = $this->handleSearchForm($request, ['className' => $className, 'query' => $query]);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;

        $templateData['error'] = $authenticationUtils->getLastAuthenticationError();
        $templateData['lastUsername'] = $authenticationUtils->getLastUsername();
        return $this->render('Security/login.html.twig', $templateData);
    }

    /**
     * @param Request $request
     * @return Response
     * @Route(path="/registration")
     */
    public function registerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $request->query->getAlnum('q');
        $className = $request->query->getAlnum('type');
        $templateData['title'] = 'Registration';
        $templateData['form'] = $this->handleCurrencyForm($request, 'app_security_register');
        if($this->currencyRedirectResponse) return $this->currencyRedirectResponse;
        $templateData['searchForm'] = $this->handleSearchForm($request, ['className' => $className, 'query' => $query]);
        if($this->searchRedirectResponse) return $this->searchRedirectResponse;

        $user = new User();
        $role = $em->getRepository('AppBundle:Role')->findOneBy(['title' => 'ROLE_USER']);
        $user->setRole($role);

        $regForm = $this->createForm(UserType::class, $user);
        $regForm->handleRequest($request);
        if($regForm->isValid() && $regForm->isSubmitted()){
            $user->setCart($this->get('cart_manager')->getCart());
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $em->persist($user);
            $em->flush();
        }
        $templateData['regForm'] = $regForm->createView();
        return $this->render('Security/registration.html.twig', $templateData);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: rus
 * Date: 15.11.16
 * Time: 19:58
 */

namespace AppBundle\Controller\Admin;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Form\Admin\UserType;

class UserController extends Controller
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(path="/admin/user/edit/{id}", requirements={"id" : "\d+"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $user = $userRepo->find($id);
        if(!$user) throw new NotFoundHttpException('User with id= '.$id.' not found.');
        dump($user);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if($form->isValid()){
            $em->flush();
            return $this->redirectToRoute('app_admin_user_edit', ['id' => $id]);
        }
        $templateData['form'] = $form->createView();
        $templateData['user'] = $user;
        //dump($form->getErrors(true));
        return $this->render('Admin/EditUser.html.twig', $templateData);
    }
}
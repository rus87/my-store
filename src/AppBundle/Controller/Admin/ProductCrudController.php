<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Product;
use AppBundle\Entity\Category;
use AppBundle\Entity\Photo;
use AppBundle\Form\Admin\ProductType;
use AppBundle\Form\Admin\PhotoType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProductCrudController extends Controller
{

    /**
     * @Route(path="admin/product/add")
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $product = new Product();
        $product->addPhoto(new Photo());
        $product->addPhoto(new Photo());
        $categories = $em->getRepository("AppBundle:Category")->findAll();
        foreach($categories as $cat)
            $catsChoice [$cat->getName()]= $cat->getName();
        $form = $this->createForm(ProductType::class, $product, ['categories' => $catsChoice]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $category = $em->getRepository("AppBundle:Category")->findOneBy(['name' => $form['category']->getData()]);
            $product->setCategory($category);
            $files = [];
            $i=1;
            foreach($product->getPhotos() as &$photo){
                $files []= $photo->getName();
                $photo->setName($i.'.'.$photo->getName()->guessExtension());
                $i++;
            }
            $em->persist($product);
            $em->flush();
            for($i=0; $i<count($files); $i++){
                $files[$i]->move($this->getParameter("photo_folder").'/'.$product->getId(),
                    $product->getPhotos()[$i]->getName());
            }

            return $this->render("Admin/AdminBase.html.twig");
        }
        return $this->render("Admin/ProductAdd.html.twig", ['form'=>$form->createView()]);
    }



    /**
     * @Route(path="admin/product/get/{id}", requirements={"id" : "\d+"})
     */
    public function readProductAction()
    {

    }

    /**
     * @Route(path="admin/product/update/{id}", requirements={"id" : "\d+"})
     */
    public function updateProductAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository("AppBundle:Product")->find($id);
        $categories = $em->getRepository("AppBundle:Category")->findAll();
        if(!$product) throw $this->createNotFoundException();
        dump($product);
        foreach($categories as $cat)
            $catsChoice [$cat->getName()]= $cat->getName();
        $form = $this->createForm(ProductType::class, $product, ['categories' => $catsChoice,'mode' => 'update']);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $category = $em->getRepository("AppBundle:Category")->findOneBy(['name' => $form['category']->getData()]);
            $product->setCategory($category);
            foreach($product->getPhotos() as &$photo)
                if($photo->isDelete()){
                    $em->remove($photo);
                    unlink($this->getParameter('photo_folder').'/'.$product->getId().'/'.$photo->getName());
                }

            $em->flush();
            dump($product);
            return $this->redirectToRoute('app_admin_productcrud_updateproduct', ['id' => $product->getId()]);
        }
        $photosPaths = [];
        foreach($product->getPhotos() as $photo)
            $photosPaths []= $this->getParameter('photo_folder_asset').'/'.$product->getId().'/'.$photo->getName();

        return $this->render("Admin/ProductUpdate.html.twig",
            ['form'=>$form->createView(), 'photos' => $photosPaths]);
    }

    /**
     * @Route(path="admin/product/add/{id}", requirements={"id" : "\d+"})
     */
    public function deleteProductAction()
    {

    }

    /**
     * @param Request $request
     * @Route(path="/upload")
     * @return Response A Response instance
     */
    public function uploadTestAction(Request $request)
    {
        $photo = new Photo();
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);
        if($form->isValid() && $form->isSubmitted())
        {
            $file = $photo->getName();
            dump($file);
            $file->move($this->getParameter('photo_folder'), $file->getClientOriginalName());
            return $this->render("Admin/AdminBase.html.twig");
        }
        return $this->render("Admin/ProductAdd.html.twig", ['form'=>$form->createView()]);
    }
}
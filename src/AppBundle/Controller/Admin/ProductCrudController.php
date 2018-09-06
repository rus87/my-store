<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(path="admin/product/add/{type}", requirements={"type": "(jacket|sweater|trousers|blouse)"})
     */
    public function addAction(Request $request, $type = 'jacket')
    {
        $navActive = ['jacket' => null, 'sweater' => null, 'trousers' => null, 'blouse' => null, ];
        $navActive[$type] = 'active';
        $em = $this->getDoctrine()->getManager();
        $productClassName = 'AppBundle\Entity\Products\\'.ucfirst($type);
        $formClassName = 'AppBundle\Form\Admin\Products\\'.ucfirst($type).'Type';
        $product = new $productClassName;
        $productCategory = $em->getRepository('AppBundle:Category')->findOneBy(['name' => $type]);
        $productAvailableCats = $em->getRepository('AppBundle:Category')->getBranchCategories($productCategory);
        $productAvailableCats []= $productCategory;
        foreach($productAvailableCats as $cat)
            $catsChoice [$cat->getName()]= $cat->getName();
        $form = $this->createForm($formClassName, $product, ['categories' => $catsChoice]);
        $form->handleRequest($request);
        if($form->isValid()){
            $productManager = $this->get('product_manager');
            $category = $em->getRepository("AppBundle:Category")->findOneBy(['name' => $form['category']->getData()]);
            $product->setCategory($category);
            $brand = $product->getBrand();
            if(! $brand->getCategories()->contains($category))
                $brand->addCategory($category);
            $photosFiles = [];
            foreach ($product->getPhotos() as $photo ) {
                $photosFiles[] = $photo->getName();
                $product->removePhoto($photo);
            }
            $mainPhoto1File = $product->getMainPhoto1()->getName();
            $mainPhoto2File = $product->getMainPhoto2()->getName();
            $product->setMainPhoto1(null);
            $product->setMainPhoto2(null);
            $em->persist($product);
            $em->flush();
            foreach($photosFiles as $file)
                $productManager->addPhoto($product->getId(), $file);
            $productManager->setMainPhoto($product->getId(), $mainPhoto1File, 1);
            $productManager->setMainPhoto($product->getId(), $mainPhoto2File, 2);
            return $this->redirectToRoute('app_admin_productcrud_add', ['type' => $type]);
        }
        return $this->render('Admin/ProductsAdd/'.ucfirst($type).'.html.twig',
            [
                'form' => $form->createView(),
                'navActive' => $navActive
            ]);

    }


    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(path="admin/product/update/{id}", requirements={"id" : "\d+"})
     */
    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $catsRepo = $em->getRepository('AppBundle:Category');
        $productManager = $this->get('product_manager');

        $productsRepo = $em->getRepository("AppBundle:Product");
        $product = $productsRepo->getById($id);
        if(!$product) throw $this->createNotFoundException();
        $tmp = explode('\\', get_class($product));
        $formClassName = 'AppBundle\Form\Admin\Products\\'.$tmp[sizeof($tmp)-1].'Type';
        $product->setNewPhoto(new Photo());
        $branchCategories = $catsRepo->getBranchCategories($product->getCategory());
        foreach($branchCategories as $cat)
            $catsChoice [$cat->getName()]= $cat->getName();
        $form = $this->createForm($formClassName, $product, ['categories' => $catsChoice,'mode' => 'update']);
        $form->handleRequest($request);
        if($form->isValid()){
            $category = $em->getRepository("AppBundle:Category")->findOneBy(['name' => $form['category']->getData()]);
            $product->setCategory($category);
            foreach($product->getPhotos() as &$photo)
                if($photo->isDelete())
                    $this->get('photo_manager')->remove($photo->getId());
            $file = $product->getNewPhoto()->getName();
            if($file)
                $productManager->addPhoto($product->getId(), $file);
            if($form['mainPhoto1']->getData() != null){
                $file = $form['mainPhoto1']->getData()->getName();
                $productManager->setMainPhoto($id, $file, 1);
            }
            if($form['mainPhoto2']->getData() != null){
                $file = $form['mainPhoto2']->getData()->getName();
                $productManager->setMainPhoto($id, $file, 2);
            }
            $em->flush();
            return $this->redirectToRoute('app_admin_productcrud_update', ['id' => $product->getId()]);
        }
        return $this->render("Admin/ProductsUpdate/".$tmp[sizeof($tmp)-1].".html.twig",
        ['form'=>$form->createView(), 'product' => $product]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(path="admin/product/delete/{id}", requirements={"id" : "\d+"})
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AppBundle:Product')->getById($id);
        $categoryName = $product->getCategory()->getName();
        if(! $product) throw $this->createNotFoundException();
        $this->get('liip_imagine.cache.manager')->remove($product->getMainPhoto2Path());
        unlink($product->getMainPhoto2Path());
        $this->get('liip_imagine.cache.manager')->remove($product->getMainPhoto1Path());
        unlink($product->getMainPhoto1Path());
        foreach($product->getPhotos() as $photo){
            $em->persist($photo);
            unlink($photo->getPath());
            $this->get('liip_imagine.cache.manager')->remove($photo->getPath());
            $em->remove($photo);
            $em->flush();
        }
        rmdir($product->getPhotosDirectory());
        $em->persist($product);
        $em->remove($product);
        $em->flush();
        return $this->redirectToRoute('app_admin_productcrud_showall', ['categoryName' => $categoryName]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(path = "/admin/products/show-all/{categoryName}")
     */
    public function showAllAction($categoryName = 'jacket')
    {
        $em = $this->getDoctrine()->getManager();
        $catsRepo = $em->getRepository('AppBundle:Category');
        $currentCat = $catsRepo->findOneBy(['name' => $categoryName]);
        $currentCatPath[] = $currentCat;
        $parents = $catsRepo->getAllParents($currentCat);
        if($parents)
            $currentCatPath = array_merge($currentCatPath, $parents);
        $currentCatPath = array_reverse($currentCatPath);
        $templateData['products'] = $this->get('product_manager')->findByCategory($currentCat->getName());
        $templateData['currentCatPath'] = $currentCatPath;
        return $this->render('Admin/Products-list.html.twig', $templateData);
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
            $file->move($this->getParameter('photo_folder'), $file->getClientOriginalName());
            return $this->render("Admin/AdminBase.html.twig");
        }
        return $this->render("Admin/ProductAdd.html.twig", ['form'=>$form->createView()]);
    }
}
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
            dump($product);
            $category = $em->getRepository("AppBundle:Category")->findOneBy(['name' => $form['category']->getData()]);
            $product->setCategory($category);
            $files = [];
            foreach($product->getPhotos() as &$photo){
                $files []= $photo->getName();
                $photo->setName(md5(uniqid()).'.'.$photo->getName()->guessExtension());
            }
            $mainPhoto1File = $product->getMainPhoto1()->getName();
            $mainPhoto2File = $product->getMainPhoto2()->getName();
            $product->getMainPhoto1()->setName(md5(uniqid()).'.'.$product->getMainPhoto1()->getName()->guessExtension());
            $product->getMainPhoto2()->setName(md5(uniqid()).'.'.$product->getMainPhoto2()->getName()->guessExtension());
            $product->updatePhotosReferences();
            $em->persist($product);
            $em->flush();
            for($i=0; $i<count($files); $i++){
                $files[$i]->move($this->getParameter("photo_folder").'/'.$product->getId(),
                    $product->getPhotos()[$i]->getName());
            }
            $mainPhoto1File->move($this->getParameter("photo_folder").'/'.$product->getId(),
                $product->getMainPhoto1()->getName());
            $mainPhoto2File->move($this->getParameter("photo_folder").'/'.$product->getId(),
                $product->getMainPhoto2()->getName());
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
                if($photo->isDelete()){
                    $this->get('liip_imagine.cache.manager')->remove($photo->getPath());
                    $em->remove($photo);
                    unlink($photo->getPath());
                }
            $file = $product->getNewPhoto()->getName();
            if($file){
                $photo = new Photo();
                $photo->setProduct($product);
                $photo->setName(md5(uniqid()).'.'.$file->guessExtension());
                $product->addPhoto($photo);
                $file->move($this->getParameter('photo_folder').'/'.$product->getId(), $photo->getName());
            }
            if($form['mainPhoto1']->getData() != null){
                $file = $form['mainPhoto1']->getData()->getName();
                if($product->getMainPhoto1() != null){
                    $this->get('liip_imagine.cache.manager')->remove($product->getMainPhoto1Path());
                    unlink($product->getMainPhoto1Path());
                    $em->remove($product->getMainPhoto1());
                }
                $product->setMainPhoto1(new Photo());
                $product->getMainPhoto1()->setName(md5(uniqid()).'.'.$file->guessExtension());
                $file->move($this->getParameter('photo_folder').'/'.$product->getId(), $product->getMainPhoto1()->getName());
            }
            if($form['mainPhoto2']->getData() != null){
                $file = $form['mainPhoto2']->getData()->getName();
                if($product->getMainPhoto2() != null){
                    $this->get('liip_imagine.cache.manager')->remove($product->getMainPhoto2Path());
                    unlink($product->getMainPhoto2Path());
                    $em->remove($product->getMainPhoto2());
                }
                $product->setMainPhoto2(new Photo());
                $product->getMainPhoto2()->setName(md5(uniqid()).'.'.$file->guessExtension());
                $file->move($this->getParameter('photo_folder').'/'.$product->getId(), $product->getMainPhoto2()->getName());
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
        $productsRepo = $em->getRepository('AppBundle:Product');
        $currentCat = $catsRepo->findOneBy(['name' => $categoryName]);
        $currentCatPath[] = $currentCat;
        $parents = $catsRepo->getAllParents($currentCat);
        if($parents)
            $currentCatPath = array_merge($currentCatPath, $parents);
        $currentCatPath = array_reverse($currentCatPath);
        //$templateData['products'] = $productsRepo->getByCategory($currentCat);
        $templateData['products'] = $this->get('product_manager')->findByCategory($currentCat->getName());
        dump($currentCatPath);
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
            dump($file);
            $file->move($this->getParameter('photo_folder'), $file->getClientOriginalName());
            return $this->render("Admin/AdminBase.html.twig");
        }
        return $this->render("Admin/ProductAdd.html.twig", ['form'=>$form->createView()]);
    }
}
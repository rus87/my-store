<?php
namespace AppBundle\Utils;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use AppBundle\Entity\Category;

class CatsListGenerator
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Router
     */
    private $router;
    private $outputHtml;
    private $rootCats;

    /**
     * SidebarCatsGenerator constructor.
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(Router $router, EntityManager $em)
    {
        $this->em = $em;
        $this->router = $router;
        $this->outputHtml = '<ul>';
        $this->rootCats = $this->em->getRepository('AppBundle:Category')->findBy(['parent' => null]);
    }

    public function generateHtml()
    {
        $this->recursiveIterator($this->rootCats);
        return $this->outputHtml.'</ul>';
    }

    private function recursiveIterator($inputCats, $level = 1)
    {
        foreach($inputCats as $cat){
            if(! $cat->getChildren()->isEmpty()){
                $this->callback($cat);
                if(! in_array($cat, $this->rootCats)){
                    $level++;
                }
                $this->outputHtml .= "<ul class='inner-cats level-$level'>";
                $this->recursiveIterator($cat->getChildren()->getValues(), $level);
                $this->outputHtml .= "</ul></li>";
            }
            else{
                $this->callback($cat);
            }
        }
    }

    private function callback(Category $cat)
    {
        $path = $this->router->generate('app_products_showbycategory', ['categoryName' => $cat->getName()]);
        if(! $cat->getChildren()->isEmpty())
            $this->outputHtml .= "<li class='has-children'><a href='$path'>".$cat->getDisplayedName().'</a>';
        else
            $this->outputHtml .= "<li class='no-children'><a  href='$path'>".$cat->getDisplayedName().'</a></li>';
    }
}
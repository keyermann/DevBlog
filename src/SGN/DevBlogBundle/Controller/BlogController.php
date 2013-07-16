<?php

namespace SGN\DevBlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Pagerfanta\Exception\NotValidCurrentPageException;

class BlogController extends Controller
{
    /**
	 * @Route("/", defaults={"page" = 1}, requirements={"page" = "\d+"})
	 * @Route("/page{page}/", requirements={"page" = "\d+"})
     * @Template()
     */
    public function indexAction($page)
    {
        $em = $this->getDoctrine()->getManager();
        $ArticleRepo = $em->getRepository('ElsassSeeraiwerESArticleBundle:Article');

	    $qb = $this->getDoctrine()->getEntityManager()->createQueryBuilder()
	        ->select('a')
	        ->from('ElsassSeeraiwerESArticleBundle:Article', 'a')
	        ->where("a.status = 'published'")
   			->orderBy('a.createDate', 'DESC');
	    ;

	    // Classe spécifique à Doctrine, il existe un équivalent pour Propel.
	    // Prend le QueryBuilder de notre requête en paramètre de son constructeur.
	    // Vous pouriez aussi utiliser un DoctrineCollectionAdapter pour paginer une collection déjà récupérée.
	    $adapter = new DoctrineORMAdapter($qb);
    	$pagerfanta = new Pagerfanta($adapter);

		try
		{
	        $entities = $pagerfanta
	            // Le nombre maximum d'éléments par page
	            ->setMaxPerPage(3)
	            // Notre position actuelle (numéro de page)
	            ->setCurrentPage($page)
	            // On récupère nos entités via Pagerfanta,
	            // celui-ci s'occupe de limiter la requête en fonction de nos réglages.
	            ->getCurrentPageResults()
	        ;
	    } 
	    catch(NotValidCurrentPageException $e)
	    {
	        throw $this->createNotFoundException("Cette page n'existe pas.");
	    }
 
	    return array(
	        'entities' 	=> $entities,
	        'pager' 	=> $pagerfanta,
	    );
	    return array();
    }

    /**
	 * @Route("/tags/page{page}/{tags}", defaults={"page" = 1}, requirements={"tags" = ".+", "page" = "\d+"})
	 * @Route("/tags/{tags}", defaults={"page" = 1}, requirements={"tags" = ".+", "page" = "\d+"})
     * @Template("SGNDevBlogBundle:Blog:index.html.twig")
     */
    public function tagsAction($page, $tags)
    {
        $em = $this->getDoctrine()->getManager();
        $ArticleRepo = $em->getRepository('ElsassSeeraiwerESArticleBundle:Article');

        $tagList = array_unique(explode("/", trim($tags, '/')));
	    $nbTagList = count($tagList);

	    $qb = $this->getDoctrine()->getEntityManager()->createQueryBuilder()
	        ->select('a')
	        ->from('ElsassSeeraiwerESArticleBundle:Article', 'a')
	        ->leftJoin("a.tags", 't')
	        ->where("a.status = 'published'")
	        ->andWhere("t.name IN ('".implode("','", $tagList)."')")
	        ->groupBy('a')
	    	->having('COUNT(a.id) = '.$nbTagList)
	    	->orderBy('a.createDate', 'DESC')
    	;

	    // Classe spécifique à Doctrine, il existe un équivalent pour Propel.
	    // Prend le QueryBuilder de notre requête en paramètre de son constructeur.
	    // Vous pouriez aussi utiliser un DoctrineCollectionAdapter pour paginer une collection déjà récupérée.
	    $adapter = new DoctrineORMAdapter($qb);
    	$pagerfanta = new Pagerfanta($adapter);

		try
		{
	        $entities = $pagerfanta
	            // Le nombre maximum d'éléments par page
	            ->setMaxPerPage(5)
	            // Notre position actuelle (numéro de page)
	            ->setCurrentPage($page)
	            // On récupère nos entités via Pagerfanta,
	            // celui-ci s'occupe de limiter la requête en fonction de nos réglages.
	            ->getCurrentPageResults()
	        ;
	    } 
	    catch(NotValidCurrentPageException $e)
	    {
	        throw $this->createNotFoundException("Cette page n'existe pas.");
	    }
 
	    return array(
	        'entities' 	=> $entities,
	        'pager' 	=> $pagerfanta,
	        'tagList'	=> $tagList,
	    );
	    return array();
    }
}

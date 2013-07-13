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
	 * @Route("/", defaults={"page" = 1})
	 * @Route("/page{page}/")
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
	            ->setMaxPerPage(10)
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
}

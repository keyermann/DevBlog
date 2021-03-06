<?php

namespace SGN\DevBlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Pagerfanta\Exception\NotValidCurrentPageException;

use ElsassSeeraiwer\ESArticleBundle\Entity\Article;
use SGN\DevBlogBundle\Entity\TagCollection;

class BlogController extends Controller
{
    /**
	 * @Route("/", defaults={"page" = 1}, requirements={"page" = "\d+"})
	 * @Route("/page{page}/", requirements={"page" = "\d+"})
     * @Template()
     */
    public function indexAction($page)
    {
    	$cmp = function($a, $b)
    	{
    		if ($a['a'] == $b['a'])
    		{
		        return 0;
		    }
    		return ($a['a'] < $b['a']) ? 1 : -1;
    	};

        $em = $this->getDoctrine()->getManager();
        $ArticleRepo = $em->getRepository('ElsassSeeraiwerESArticleBundle:Article');
        $nowDate = new \DateTime('now');

	    $qbIgnore = $this->getDoctrine()->getEntityManager()->createQueryBuilder()
	        ->select('a.id')
	        ->from('ElsassSeeraiwerESArticleBundle:Article', 'a')
	        ->leftJoin("a.tags", 't')
	        ->where("t.name = 'pour-les-curieux'");
	    ;

	    $ignoreIdRes = $qbIgnore->getQuery()->getArrayResult();
	    $ignoreId= array();
	    foreach ($ignoreIdRes as $key => $value) {
	    	$ignoreId[] = $value['id'];
	    }

        $qbCount = $this->getDoctrine()->getEntityManager()->createQueryBuilder()
        	->select('count(a.id)')
        	->from('ElsassSeeraiwerESArticleBundle:Article','a')
	        ->where("a.status = 'published'")
	        ->andWhere("a.publicationDate < '".$nowDate->format('d-m-Y H:i').":00'")
        ;
	    if(count($ignoreId) > 0){ $qbCount->andWhere("a.id NOT IN (".implode(",", $ignoreId).")"); }
	    $count = $qbCount->getQuery()->getSingleScalarResult();

	    $qb = $this->getDoctrine()->getEntityManager()->createQueryBuilder()
	        ->select('a')
	        ->from('ElsassSeeraiwerESArticleBundle:Article', 'a')
	        ->where("a.status = 'published'")
	        ->andWhere("a.publicationDate < '".$nowDate->format('d-m-Y H:i').":00'")
        ;

	    if(count($ignoreId) > 0){ $qb->andWhere("a.id NOT IN (".implode(",", $ignoreId).")"); }
   		$qb->orderBy('a.publicationDate', 'DESC');

        $TagRepo = $em->getRepository('ElsassSeeraiwerESArticleBundle:Tag');
        $TagMenuEntities = $TagRepo->getAllByCount();

        foreach ($TagMenuEntities as $key => $value) {
        	$TagMenuEntities[$key]['a'] = $value[0]->getPublishedArticlesLength();
        }
    	uasort($TagMenuEntities, 'cmp');

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
	        'tagElems'	=> $TagMenuEntities,
	        'nbArticle'	=> $count,
	    );
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
        $nowDate = new \DateTime('now');

        $tagList = array_unique(explode("/", trim($tags, '/')));
	    $nbTagList = count($tagList);

        $qbCount = $this->getDoctrine()->getEntityManager()->createQueryBuilder()
        	->select('a.id')
        	->from('ElsassSeeraiwerESArticleBundle:Article','a')
	        ->leftJoin("a.tags", 't')
	        ->where("a.status = 'published'")
	        ->andWhere("a.publicationDate < '".$nowDate->format('d-m-Y H:i').":00'")
	        ->andWhere("t.name IN ('".implode("','", $tagList)."')")
	        ->groupBy('a')
	    	->having('COUNT(a.id) = '.$nbTagList);
	    $res = $qbCount->getQuery()->getResult();
	    $count = count($res);

	    $qb = $this->getDoctrine()->getEntityManager()->createQueryBuilder()
	        ->select('a')
	        ->from('ElsassSeeraiwerESArticleBundle:Article', 'a')
	        ->leftJoin("a.tags", 't')
	        ->where("a.status = 'published'")
	        ->andWhere("a.publicationDate < '".$nowDate->format('d-m-Y H:i').":00'")
	        ->andWhere("t.name IN ('".implode("','", $tagList)."')")
	        ->groupBy('a')
	    	->having('COUNT(a.id) = '.$nbTagList)
	    	->orderBy('a.publicationDate', 'DESC')
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

        $TagMenuEntities = $this->createTagListFromArticles($entities, $tagList);
 
	    return array(
	        'entities' 	=> $entities,
	        'pager' 	=> $pagerfanta,
	        'tagList'	=> $tagList,
	        'tagElems'	=> $TagMenuEntities,
	        'nbArticle'	=> $count,
	    );
    }

    /**
	 * @Route("/article/{slug}/")
     * @Template()
     */
    public function articleAction($slug){
    }


    /**
	 * @Route("/search-create/")
     * @Template()
     */
    public function searchCreateAction(Request $request)
    {
    	$tagCollection = new TagCollection();

    	$formBuild = $this->createFormBuilder($tagCollection, array('attr' => array('class' => 'recherche')))
    		->setAction($this->generateUrl('sgn_devblog_blog_searchcreate'))
    		->add('tags', 'text', array(
    			'label'		=> false,
    			'attr'		=> array(
    				'class'		=> 'input-xxlarge input-search',
				),
			))
            ->add('search', 'submit', array(
            	'attr'		=> array(
            		'label'		=> 'Rechercher',
            		'class'		=> 'btn btn-primary',
        		),
        	));

    	if ($this->get('security.context')->isGranted('ROLE_DEV')) {
        	$formBuild->add('create', 'submit', array(
            	'attr'		=> array(
            		'label'		=> 'Créer article',
            		'class'		=> 'btn btn-success',
        		),
        	));
        }

        $form = $formBuild->getForm();

        $em = $this->getDoctrine()->getManager();
        $TagRepo = $em->getRepository('ElsassSeeraiwerESArticleBundle:Tag');
        $TagMenuEntities = $TagRepo->getAllByName();
        $searchTagList = array();

        foreach ($TagMenuEntities as $tag) {
        	$searchTagList[] = $tag->getName();
        }
    	$form->handleRequest($request);

    	if ($form->isValid()) {
    		if($form->get('search')->isClicked()) 
    		{
    			return $this->redirect($this->generateUrl('sgn_devblog_blog_tags_1', array('tags' => str_replace(',', '/', $tagCollection->getTags()))));
		    }
		    elseif($form->get('create')->isClicked())
		    {
		    	return $this->forward('ElsassSeeraiwerESArticleBundle:ArticleDB:addByTitle', array('titleTags'  => $tagCollection->getTags()));
		    }
	    }

        return array(
            'form' 				=> $form->createView(),
            'searchTagList'		=> $searchTagList,
        );
    }

    private function createTagListFromArticles($articles, $tagListIgnore)
    {
    	$TagEntities = array();

    	$cmp = function($a, $b)
    	{
    		if ($a['a'] == $b['a'])
    		{
		        return 0;
		    }
    		return ($a['a'] < $b['a']) ? 1 : -1;
    	};

    	foreach($articles as $article)
    	{
    		$tags = $article->getTags();
    		foreach ($tags as $tag)
    		{
    			if(in_array($tag->getName(), $tagListIgnore))
    			{
    				continue;
    			}
    			if(!isset($TagEntities[$tag->getName()]))
    			{
    				$TagEntities[$tag->getName()][0] = $tag;
    				$TagEntities[$tag->getName()]['a'] = 1;
    			}
    			else 
    			{
    				$TagEntities[$tag->getName()]['a']++;
    			}
    		}
    	}
    	uasort($TagEntities, 'cmp');

    	return $TagEntities;
    }
}

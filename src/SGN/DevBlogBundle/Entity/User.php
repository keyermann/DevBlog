<?php

namespace SGN\DevBlogBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sgnmail_user")
 * @ORM\Entity(repositoryClass="SGN\DevBlogBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="text", nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="text", nullable=true)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="initiales", type="text", nullable=true)
     */
    private $initiales;

    /**
     * @var string
     *
     * @ORM\Column(name="categorie", type="text", nullable=true)
     */
    private $categorie;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="unite_id", referencedColumnName="id", nullable=true)
     **/
    private $unite;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return User
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     * @return User
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set initiales
     *
     * @param string $initiales
     * @return User
     */
    public function setInitiales($initiales)
    {
        $this->initiales = $initiales;

        return $this;
    }

    /**
     * Get initiales
     *
     * @return string 
     */
    public function getInitiales()
    {
        return $this->initiales;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set categorie
     *
     * @param string $categorie
     * @return User
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return string 
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set unite
     *
     * @param \SGN\DevBlogBundle\Entity\User $unite
     * @return User
     */
    public function setUnite(\SGN\DevBlogBundle\Entity\User $unite = null)
    {
        $this->unite = $unite;

        return $this;
    }

    /**
     * Get unite
     *
     * @return \SGN\DevBlogBundle\Entity\User 
     */
    public function getUnite()
    {
        return $this->unite;
    }
}
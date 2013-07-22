<?php 

namespace SGN\DevBlogBundle\Entity;

use ElsassSeeraiwer\ESArticleBundle\Entity\Tag;

class TagCollection
{
    protected $tags;

    public function setTags($tags)
    {
    	$this->tags = $tags;

        return $this;
    }

    public function getTags()
    {
        return $this->tags;
    }
}
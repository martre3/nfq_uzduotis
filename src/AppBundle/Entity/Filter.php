<?php

namespace AppBundle\Entity;

class Filter
{
    protected $searchPattern;
    protected $sort;

    public function getSearchPattern()
    {
        return $this->searchPattern;
    }

    public function setSearchPattern($searchPattern)
    {
        $this->searchPattern = $searchPattern;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }
}
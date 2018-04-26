<?php

namespace ElasticWrapper;

class Paginator
{
    public $size = 12;
    public $page = 1;
    public $margin = 2;

    public $prev;
    public $next;
    public $current;
    public $first;
    public $last;

    public $count;

    public $search;
    /**
     * constructor
     * @param object  $search class Search or SearchI18n
     * @param integer $size   number of items per page
     * @param integer $page   number of current page
     * @param integer $margin [description]
     */
    public function __construct(
        $search,
        $size = false,
        $page = false,
        $margin = false
    ) {
        $this->search = $search;
        if ($size) {
            $this->size = $size;
        }
        if ($page) {
            $this->page = $page;
        }
        if ($margin) {
            $this->margin = $margin;
        }
        $this->calc();
    }

    public function results()
    {
        return $this->search->results($this->getParams());
    }

    public function getParams()
    {
        $params = $this->search->getParams();
        $params['body']['size'] = $this->size;
        $params['body']['from'] = ($this->page - 1) * $this->size;
        return $params;
    }

    public function calc()
    {
        $this->count = $this->search->count();
        $max = ceil($this->count / $this->size);
        $this->prev  = $this->page > 1 ? $this->page - 1 : null;
        $this->next  = $this->page < $max ? $this->page + 1 : null;
        $this->first = $this->page > 1 ? 1 : null;
        $this->last  = $this->page < $max ? $max : null;
    }

    public function items($arrows = false)
    {
        $result = [];
        if ($arrows) {
            if ($this->first) {
                $result[] = new Page($this->first, 'first');
            }
            if ($this->prev) {
                $result[] = new Page($this->prev, 'prev');
            }
        }
        $from = max(1, $this->page - $this->margin);
        $to   = min($this->page + $this->margin, $this->last);
        for ($i = $from; $i <= $to; $i++) {
            $page = new Page($i);
            if ($i == $this->page) {
                $page->active = true;
            }
            $result[] = $page;
        }
        if ($arrows) {
            if ($this->next) {
                $result[] = new Page($this->next, 'next');
            }
            if ($this->last) {
                $result[] = new Page($this->last, 'last');
            }
        }
        return $result;
    }
}

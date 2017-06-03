<?php

namespace Pho\Kernel\Foundation;

use Pho\Framework;

class Draft {

    protected $context;
    protected $author;

    public function __construct(Framework\Actor $author, Framework\Frame $context) {
        $this->context = $context;
        $this->author = $author;
    }

    public function post(?Framework\Frame $context = null): Framework\Object
    {
        if(!is_null($context))
            $this->context = $context;
        return new Framework\Object($this->author, $this->context);
    }

}
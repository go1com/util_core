<?php

namespace go1\util\model;

class PurchaseRequest
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var array
     */
    public $original;

    /**
     * @var integer
     */
    public $portalId;

    /**
     * @var integer
     */
    public $status;

    /**
     * @var string
     */
    public $responseDate;
}

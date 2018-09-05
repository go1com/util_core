<?php

namespace go1\util\model;

use go1\util\enrolment\PurchaseRequestStatus;

class PurchaseRequest
{
    /**
     * @var integer
     */
    public $id = 0;

    /**
     * @var array
     */
    public $original = [];

    /**
     * @var integer
     */
    public $portalId = 0;

    /**
     * @var integer
     */
    public $status = PurchaseRequestStatus::NOT_ACTIONED;

    /**
     * @var string
     */
    public $responseDate = '';
}

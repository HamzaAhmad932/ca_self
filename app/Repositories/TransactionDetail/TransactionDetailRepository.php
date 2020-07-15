<?php


namespace App\Repositories\TransactionDetail;

class TransactionDetailRepository //extends BaseRepository implements RepositoryInterface
{
    /**
     * @var string|null
     */
    protected $tenantKey;
    /**
     * @var string|null
     */
    protected $tenantValue;

    /**
     * TransactionDetailRepository constructor.
     * @param string|null $tenantKey
     * @param string|null $tenantValue
     */
//    public function __construct(string $tenantKey = null, string $tenantValue = null)
//    {
//        $this->tenantKey   = $tenantKey;
//        $this->tenantValue = $tenantValue;
//        $this->setModelName(TransactionDetail::class);
//    }
}
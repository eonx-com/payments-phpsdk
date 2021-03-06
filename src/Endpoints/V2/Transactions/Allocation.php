<?php
declare(strict_types=1);

namespace EoneoPay\PhpSdk\Endpoints\V2\Transactions;

use EoneoPay\PhpSdk\Endpoints\V2\Ewallet;
use EoneoPay\PhpSdk\Endpoints\V2\Transactions\Allocations\Record;
use EoneoPay\PhpSdk\Traits\V2\Transactions\AllocationTrait;
use LoyaltyCorp\SdkBlueprint\Sdk\Entity;

/**
 * @method string|null getAmount()
 * @method string|null getCreatedAt()
 * @method Ewallet|null getEwallet()
 * @method Record[]|null getRecords()
 * @method string|null getUpdatedAt()
 */
class Allocation extends Entity
{
    use AllocationTrait;

    /**
     * {@inheritdoc}
     */
    public function uris(): array
    {
        // There are no actions directly on allocations
        return [];
    }
}

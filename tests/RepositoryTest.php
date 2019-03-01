<?php
declare(strict_types=1);

namespace Tests\EoneoPay\PhpSdk;

use EoneoPay\PhpSdk\Repository;
use LoyaltyCorp\SdkBlueprint\Sdk\Interfaces\EntityInterface;
use Tests\EoneoPay\PhpSdk\Stubs\Entities\EntityStub;
use Tests\EoneoPay\PhpSdk\Stubs\Managers\EoneoPayApiManagerStub;

/**
 * @covers \EoneoPay\PhpSdk\Repository
 */
class RepositoryTest extends TestCase
{
    /**
     * Test that find all will return expected number of entities.
     *
     * @return void
     */
    public function testFindAll(): void
    {
        $entities = $this->getRepository(new EntityStub())->findAll('api-key');

        self::assertCount(1, $entities);
    }

    /**
     * Test that find by id will return expected entity.
     *
     * @return void
     */
    public function testFindById(): void
    {
        $expected = new EntityStub(['entityId' => $this->generateId()]);

        $actual = $this->getRepository($expected)->findById($expected->getEntityId() ?? '', 'api-key');

        self::assertInstanceOf(EntityStub::class, $actual);
        self::assertSame(
            $expected->getEntityId(),
            ($actual instanceof EntityStub) === true ? $actual->getEntityId() : null
        );
    }

    /**
     * Get repository.
     *
     * @param \LoyaltyCorp\SdkBlueprint\Sdk\Interfaces\EntityInterface|null $entity
     *
     * @return \EoneoPay\PhpSdk\Repository
     */
    private function getRepository(?EntityInterface $entity = null): Repository
    {
        return new Repository(
            new EoneoPayApiManagerStub($entity),
            EntityStub::class
        );
    }
}
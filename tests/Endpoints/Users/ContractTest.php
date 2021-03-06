<?php
declare(strict_types=1);

namespace Tests\EoneoPay\PhpSdk\Endpoints\Users;

use EoneoPay\PhpSdk\Endpoints\Ewallet;
use EoneoPay\PhpSdk\Endpoints\User;
use EoneoPay\PhpSdk\Endpoints\Users\Contract;
use Tests\EoneoPay\PhpSdk\TestCase;

/**
 * @covers \EoneoPay\PhpSdk\Endpoints\Users\Contract
 */
final class ContractTest extends TestCase
{
    /**
     * Test contract fees create or update.
     *
     * @return void
     */
    public function testCreateContractFees(): void
    {
        $contract = $this->createApiManager(
            [
                'action' => 'debit',
                'created_at' => '2019-02-25T05=>43=>31Z',
                'currency' => 'AUD',
                'ewallet' => [
                    'created_at' => '2019-02-24T23=>34=>11Z',
                    'currency' => 'AUD',
                    'id' => '6e967a8e9971aab24d2db4e932ca1a06',
                    'pan' => 'Y...K8Y7',
                    'primary' => true,
                    'reference' => 'YNGANFK8Y7',
                    'type' => 'ewallet',
                    'updated_at' => '2019-02-24T23=>34=>11Z',
                    'user' => [
                        'created_at' => '2019-02-24T23=>34=>11Z',
                        'email' => 'examples@user.test',
                        'updated_at' => '2019-02-24T23=>34=>11Z',
                    ],
                ],
                'fixed_fee' => '0.02',
                'group' => 'Mastercard',
                'type' => 'contract',
                'updated_at' => '2019-02-26T03=>19=>03Z',
                'user' => [
                    'created_at' => '2019-02-24T23=>34=>11Z',
                    'email' => 'examples@user.test',
                    'updated_at' => '2019-02-24T23=>34=>11Z',
                ],
                'variable_rate' => '0.10',
            ],
            200
        )->create((string)\getenv('PAYMENTS_API_KEY'), new Contract(
            [
                'group' => 'mastercard',
                'currency' => 'AUD',
                'fixed_fee' => '0.02',
                'variable_rate' => '0.10',
                'user' => new User(['id' => 'external-user-id']),
            ]
        ));

        self::assertInstanceOf(Contract::class, $contract);

        /**
         * @var \EoneoPay\PhpSdk\Endpoints\Users\Contract $contract
         *
         * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises assertion
         */
        self::assertInstanceOf(Ewallet::class, $contract->getEwallet());
    }

    /**
     * Test Contract fees getters.
     *
     * @return void
     */
    public function testGetters(): void
    {
        $user = new User(['id' => 'external-user-id']);
        $ewallet = new Ewallet([]);
        $contract = new Contract([
            'action' => 'debit',
            'currency' => 'AUD',
            'ewallet' => $ewallet,
            'group' => 'mastercard',
            'fixed_fee' => '0.02',
            'type' => 'contract',
            'user' => $user,
            'variable_rate' => '0.10',
        ]);

        self::assertSame('debit', $contract->getAction());
        self::assertSame('mastercard', $contract->getGroup());
        self::assertSame('AUD', $contract->getCurrency());
        self::assertSame($ewallet, $contract->getEwallet());
        self::assertSame('0.02', $contract->getFixedFee());
        self::assertSame('0.10', $contract->getVariableRate());
        self::assertSame($user, $contract->getUser());
    }

    /**
     * Test get endpoint is successful.
     *
     * @return void
     */
    public function testGetContractFees(): void
    {
        $apiManager = $this->createApiManager(
            [
                'action' => 'debit',
                'created_at' => '2019-02-25T05:43:31Z',
                'currency' => 'AUD',
                'ewallet' => [
                    'created_at' => '2019-02-24T23:34:11Z',
                    'currency' => 'AUD',
                    'id' => '6e967a8e9971aab24d2db4e932ca1a06',
                    'pan' => 'Y...K8Y7',
                    'primary' => true,
                    'reference' => 'YNGANFK8Y7',
                    'type' => 'ewallet',
                    'updated_at' => '2019-02-24T23:34:11Z',
                    'user' => [
                        'created_at' => '2019-02-24T23:34:11Z',
                        'email' => 'examples@user.test',
                        'id' => '3T93F7TXCVGX4ZV7AFW2',
                        'updated_at' => '2019-02-24T23:34:11Z',
                    ],
                ],
                'fixed_fee' => '0.02',
                'group' => 'Mastercard',
                'type' => 'contract',
                'updated_at' => '2019-02-26T03:19:03Z',
                'user' => [
                    'created_at' => '2019-02-24T23:34:11Z',
                    'email' => 'examples@user.test',
                    'id' => '3T93F7TXCVGX4ZV7AFW2',
                    'updated_at' => '2019-02-24T23:34:11Z',
                ],
                'variable_rate' => '0.10',
            ],
            201
        );

        /** @var \EoneoPay\PhpSdk\Endpoints\Users\Contract $contract */
        $contract = $apiManager->findOneBy(
            Contract::class,
            (string)\getenv('PAYMENTS_API_KEY'),
            ['userId' => '3T93F7TXCVGX4ZV7AFW2']
        );

        self::assertSame('0.10', $contract->getVariableRate());
        self::assertSame('Mastercard', $contract->getGroup());
        self::assertSame('AUD', $contract->getCurrency());
        self::assertSame('0.02', $contract->getFixedFee());
        self::assertSame('debit', $contract->getAction());
        self::assertInstanceOf(User::class, $contract->getUser());
        self::assertInstanceOf(Ewallet::class, $contract->getEwallet());
    }

    /**
     * Ensure uris have correct endpoints.
     *
     * @return void
     */
    public function testUris(): void
    {
        $user = new User([]);
        $contract = new Contract([
            'user' => $user
        ]);

        $createUri = \sprintf('/users/%s/contracts', $user->getId());
        $getUri = \sprintf('/users/%s/contracts', $user->getId());

        self::assertSame(['create' => $createUri, 'get' => $getUri], $contract->uris());
    }
}

<?php
declare(strict_types=1);

namespace Tests\EoneoPay\PhpSdk\Endpoints;

use EoneoPay\PhpSdk\Endpoints\Balance;
use EoneoPay\PhpSdk\Endpoints\Ewallet;
use EoneoPay\PhpSdk\Endpoints\User;
use EoneoPay\PhpSdk\Exceptions\ClientException;
use Tests\EoneoPay\PhpSdk\TestCase;

/**
 * @covers \EoneoPay\PhpSdk\Endpoints\Ewallet
 */
final class EwalletTest extends TestCase
{
    /**
     * Test Ewallet details are returned back.
     *
     * @return void
     */
    public function testEwalletBalance(): void
    {
        $response = [
            'balances' => [
                'available' => '123.45',
                'balance' => '234.56',
                'credit_limit' => '0.00',
            ],
            'created_at' => '2019-02-25T02=>40=>32Z',
            'currency' => 'AUD',
            'id' => '6524e7d900805b9de1cc91d4864988ec',
            'pan' => 'J...ZAR0',
            'primary' => false,
            'reference' => 'JEKYYFZAR0',
            'type' => 'ewallet',
            'updated_at' => '2019-02-25T02=>40=>32Z',
            'user' => [
                'created_at' => '2019-02-22T03=>09=>44Z',
                'email' => 'example@user.test',
                'updated_at' => '2019-02-22T03=>09=>44Z',
            ],
        ];
        $expected = new Balance([
            'available' => '123.45',
            'balance' => '234.56',
            'credit_limit' => '0.00',
        ]);

        $ewallet = $this->createApiManager($response, 200)
            ->findOneBy(
                Ewallet::class,
                (string)\getenv('PAYMENTS_API_KEY'),
                ['reference' => 'JEKYYFZAR0']
            );

        self::assertSame('JEKYYFZAR0', ($ewallet instanceof Ewallet) ? $ewallet->getReference() : null);
        self::assertInstanceOf(User::class, ($ewallet instanceof Ewallet) ? $ewallet->getUser() : null);
        // check if it has balances
        self::assertInstanceOf(Balance::class, ($ewallet instanceof Ewallet) ? $ewallet->getBalances() : null);
        self::assertEquals($expected, ($ewallet instanceof Ewallet) ? $ewallet->getBalances() : null);
    }

    /**
     * Test creation of ewallet.
     *
     * @return void
     */
    public function testEwalletCreation(): void
    {
        $jsonResponse = [
            'created_at' => '2019-02-26T00=>14=>25Z',
            'balances' => [
                'available' => '123.45',
                'balance' => '234.56',
                'credit_limit' => '0.00',
            ],
            'id' => 'dad99a43563c72a19a99aae4b1605b49',
            'pan' => 'W...J3X7',
            'primary' => false,
            'reference' => 'WCMKZAJ3X7',
            'type' => 'ewallet',
            'updated_at' => '2019-02-26T00=>14=>25Z',
            'user' => [
                'created_at' => '2019-02-22T03=>09=>44Z',
                'email' => 'example@user.test',
                'updated_at' => '2019-02-22T03=>09=>44Z',
            ],
        ];
        $eoneoPayApiManager = $this->createApiManager($jsonResponse, 201);

        /** @var \EoneoPay\PhpSdk\Endpoints\Ewallet $ewallet */
        $ewallet = $eoneoPayApiManager->create((string)\getenv('PAYMENTS_API_KEY'), new Ewallet());

        self::assertIsString($ewallet->getId());
        self::assertNotEmpty($ewallet->getType());
        self::assertInstanceOf(User::class, $ewallet->getUser());
        self::assertInstanceOf(Balance::class, $ewallet->getBalances());
    }

    /**
     * Test if exception code is covered.
     *
     * @return void
     */
    public function testExceptionIsThrownOnEwalletCreationWithWrongKey(): void
    {
        $this->expectException(ClientException::class);
        $this->createApiManager([
            'code' => 4401,
            'message' => 'Unauthorised.',
            'sub_code' => 1,
            'time' => '2019-02-25T02=>31=>59Z',
        ], 401)->create('wrong_key', new Ewallet());
    }

    /**
     * Test to check if Uri has been entered.
     *
     * @return void
     */
    public function testUriIsCreated(): void
    {
        $ewallet = new Ewallet();
        self::assertCount(3, $ewallet->uris());
    }
}

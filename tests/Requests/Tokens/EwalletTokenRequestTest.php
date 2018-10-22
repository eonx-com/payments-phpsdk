<?php
declare(strict_types=1);

namespace Tests\EoneoPay\PhpSdk\Requests\Tokens;

use EoneoPay\PhpSdk\Requests\Endpoints\Tokens\EwalletTokenRequest;
use Tests\EoneoPay\PhpSdk\RequestTestCase;

/**
 * @covers \EoneoPay\PhpSdk\Requests\Endpoints\Tokens\EwalletTokenRequest
 */
class EwalletTokenRequestTest extends RequestTestCase
{
    /**
     * Test a successful bank account tokenise request.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testCreateTokenSuccessfully(): void
    {
        $data = $this->getTokenisedData();

        /** @var \EoneoPay\PhpSdk\Responses\Endpoints\Tokens\TokenisedEndpoint $response */
        $response = $this->createClient($data)->create(new EwalletTokenRequest([
            'ewallet' => $this->getEwallet()
        ]));

        self::assertSame($data['name'], $response->getName());
        self::assertSame($data['token'], $response->getToken());
    }
}
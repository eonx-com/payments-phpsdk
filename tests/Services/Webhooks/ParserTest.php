<?php
declare(strict_types=1);

namespace Tests\EoneoPay\PhpSdk\Services\Webhooks;

use EoneoPay\PhpSdk\Endpoints\PaymentSource;
use EoneoPay\PhpSdk\Endpoints\PaymentSources\BankAccount;
use EoneoPay\PhpSdk\Endpoints\Transaction;
use EoneoPay\PhpSdk\Services\Webhooks\Exceptions\InvalidEntityClassException;
use EoneoPay\PhpSdk\Services\Webhooks\Exceptions\WebhookParserValidationException;
use EoneoPay\PhpSdk\Services\Webhooks\Parser;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\EoneoPay\PhpSdk\TestCases\ValidationEnabledTestCase;

/**
 * @covers \EoneoPay\PhpSdk\Services\Webhooks\Parser
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) High coupling required to fully test the parser.
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength) Long methods for JSON.
 */
final class ParserTest extends ValidationEnabledTestCase
{
    /**
     * Gets the request scenarios that should cause one or more validation failures for testing.
     *
     * @return mixed[]
     */
    public function getInvalidRequestScenarios(): iterable
    {
        yield 'Null values' => [
            'targetClass' => Transaction::class,
            'request' => new Request(
                'POST',
                '/listen/eoneopay/transaction',
                [],
                <<<'JSON'
{
    "action": null,
    "allocation": null,
    "amount": null,
    "state": null
}
JSON
            ),
            'expected' => [
                'action' => ['This value should not be blank.'],
                'amount' => ['This value should not be blank.'],
                'state' => ['This value should not be blank.'],
            ],
        ];

        yield 'Empty allocation object and nulls' => [
            'targetClass' => Transaction::class,
            'request' => new Request(
                'POST',
                '/listen/eoneopay/transaction',
                [],
                <<<'JSON'
{
    "action": null,
    "allocation": {},
    "amount": null,
    "state": null
}
JSON
            ),
            'expected' => [
                'action' => ['This value should not be blank.'],
                'allocation.amount' => ['This value should not be blank.'],
                'allocation.ewallet' => ['This value should not be blank.'],
                'amount' => ['This value should not be blank.'],
                'state' => ['This value should not be blank.'],
            ],
        ];

        yield 'Invalid values' => [
            'targetClass' => Transaction::class,
            'request' => new Request(
                'POST',
                '/listen/eoneopay/transaction',
                [],
                <<<'JSON'
{
    "action": false,
    "allocation": false,
    "amount": false,
    "approved": "hello",
    "createdAt": false,
    "finalisedAt": false,
    "fundingSource": null,
    "metadata": false,
    "parent": false,
    "paymentDestination": null,
    "paymentSource": null,
    "response": false,
    "security": false,
    "state": false,
    "status": false,
    "transactionId": false,
    "updatedAt": false,
    "user": false
}
JSON
            ),
            'expected' => [
                'action' => [
                    'This value should not be blank.',
                    'This value should be of type string.',
                ],
                'allocation.amount' => ['This value should not be blank.'],
                'allocation.ewallet' => ['This value should not be blank.'],
                'approved' => ['This value should be of type bool.'],
                'createdAt' => [
                    'This value is not a valid datetime.',
                    'This value should be of type string.',
                ],
                'finalisedAt' => [
                    'This value is not a valid datetime.',
                    'This value should be of type string.',
                ],
                'metadata' => ['This value should be of type array.'],
                'response' => ['This value should be of type array.'],
                'state' => [
                    'This value should not be blank.',
                    'This value should be positive.',
                    'This value should be of type int.',
                ],
                'status' => ['This value should be of type string.'],
                'transactionId' => ['This value should be of type string.'],
                'updatedAt' => [
                    'This value is not a valid datetime.',
                    'This value should be of type string.',
                ],
            ],
        ];
    }

    /**
     * Gets various test webhook requests to ensure successfull parsing.
     *
     * @return mixed[]
     */
    public function getValidRequestScenarios(): iterable
    {
        yield 'Token Added' => [
            'paymentSource' => PaymentSource::class,
            'request' => new Request(
                'POST',
                '/listen/eoneopay/token',
                [],
                <<<'JSON'
{
    "country": "AU",
    "created_at": "2019-07-31T06:08:07Z",
    "currency": "AUD",
    "customer": {"email": "example@example.com"},
    "id": "cc0a468f1fb821f457977d8f6b7f3f63",
    "name": "User Name",
    "number": "987654321",
    "one_time": false,
    "pan": "123-456...4321",
    "prefix": "123-456",
    "token": "RPW2NYUJCGHFJ72WTDZ1",
    "type": "bank_account",
    "updated_at": "2019-07-31T06:08:07Z"
}
JSON
            ),
        ];

        yield 'Token Revocation' => [
            'paymentSource' => PaymentSource::class,
            'request' => new Request(
                'POST',
                '/listen/eoneopay/token_revoke',
                [],
                <<<'JSON'
{
    "country": "AU",
    "created_at": "2019-07-31T06:08:07Z",
    "currency": "AUD",
    "customer": {"email": "customer@example.com"},
    "id": "cc0a468f1fb821f457977d8f6b7f3f63",
    "name": "User Name",
    "number": "987654321",
    "one_time": false,
    "pan": "123-456...4321",
    "prefix": "123-456",
    "token": "FDJ9934242YBP3C2ZC43",
    "type": "bank_account",
    "updated_at": "2019-07-31T06:08:07Z"
}
JSON
            ),
        ];

        yield 'Transaction Creation' => [
            'paymentSource' => Transaction::class,
            'request' => new Request(
                'POST',
                '/listen/eoneopay/transaction',
                [],
                <<<'JSON'
{
    "action": "debit",
    "amount": {
        "currency": "AUD",
        "payment_fee": "0.12",
        "subtotal": "99.88",
        "total": "100.00"
    },
    "allocation": {
        "amount": "5.2",
        "ewallet": {
            "balances": {
                "available": "162.45",
                "balance": "42.45",
                "credit_limit": "120.00"
            },
            "id": "17ce4717231cdfab75d9227abbc179",
            "hash": "xyz",
            "pan": "K...WCB7",
            "reference": "ACB"
        },
        "records": [
            {
                "amount": "1.20",
                "ewallet": {
                    "id": "17ce4717231cdfab75d9227abbc179",
                    "hash": "xyz",
                    "pan": "K...WCB7",
                    "reference": "ACB"
                }
            }
        ]
    },
    "approved": true,
    "created_at": "2019-02-20T00:54:34Z",
    "id": "ORDER_EXT_ID",
    "metadata": [],
    "parent": null,
    "payment_destination": {
        "created_at": "2019-02-17T22:07:41Z",
        "currency": "AUD",
        "hash": "xyz",
        "id": "17ce7c53540eccc9fba13b755d922721",
        "pan": "K...WCB7",
        "primary": true,
        "reference": "KETYDFWCB7",
        "type": "ewallet",
        "updated_at": "2019-02-17T22:07:41Z",
        "user": {
            "created_at": "2019-02-17T22:07:41Z",
            "email": "example@user.test",
            "updated_at": "2019-02-17T22:07:41Z"
        }
    },
    "payment_source": {
        "bin": {
            "bin": "512345",
            "category": "Standard",
            "country": "EC",
            "created_at": "2019-02-17T22:10:05Z",
            "funding_source": "CREDIT",
            "issuer": "BANCO DEL PICHINCHA, C.A.",
            "prepaid": null,
            "scheme": "MASTERCARD",
            "updated_at": "2019-02-17T22:10:05Z"
        },
        "created_at": "2019-02-17T22:10:05Z",
        "expiry": {
            "month": "11",
            "year": "2099"
        },
        "facility": "Mastercard",
        "id": "1a05c6ac43c7a93088a7bff15e3625f4",
        "hash": "xyz",
        "name": "User Name",
        "pan": "512345...0008",
        "token": "UETACKCYRFNYAXYR8466",
        "type": "credit_card",
        "updated_at": "2019-02-17T22:10:06Z"
    },
    "response": {
        "acquirer_code": "00",
        "acquirer_message": "Approved",
        "gateway_message": "APPROVED"
    },
    "security": null,
    "state": 82,
    "status": "completed",
    "transaction_id": "TRANS_EXT_ID",
    "updated_at": "2019-02-20T00:54:35Z",
    "user": {
        "created_at": "2019-02-17T22:07:41Z",
        "email": "example@example.net",
        "updated_at": "2019-02-17T22:07:41Z"
    }
}
JSON
            ),
        ];

        yield 'Transaction minimal Transaction' => [
            'paymentSource' => Transaction::class,
            'request' => new Request(
                'POST',
                '/listen/eoneopay/transaction',
                [],
                <<<'JSON'
{
    "action": "debit",
    "amount": {
        "currency": "AUD",
        "payment_fee": "0.12",
        "subtotal": "99.88",
        "total": "100.00"
    },
    "approved": true,
    "id": "ORDER_EXT_ID",
    "metadata": [],
    "parent": null,
    "payment_destination": {
        "id": "17ce7c53540eccc9fba13b755d922721",
        "type": "ewallet"
    },
    "payment_source": {
        "id": "1a05c6ac43c7a93088a7bff15e3625f4",
        "type": "credit_card"
    },
    "response": {
        "acquirer_code": "00",
        "acquirer_message": "Approved",
        "gateway_message": "APPROVED"
    },
    "security": null,
    "state": 82,
    "status": "completed",
    "transaction_id": "TRANS_EXT_ID",
    "user": {
        "email": "example@example.net"
    }
}
JSON
            ),
        ];
    }

    /**
     * Tests that the 'parseRequest' method accepts a Request instance and returns an instance of the provided
     * $targetClass class name.
     *
     * @param string $targetClass
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return void
     *
     * @throws \EoneoPay\PhpSdk\Services\Webhooks\Exceptions\InvalidEntityClassException
     * @throws \EoneoPay\PhpSdk\Services\Webhooks\Exceptions\WebhookParserValidationException
     *
     * @dataProvider getValidRequestScenarios
     */
    public function testParseRequestSuccessful(string $targetClass, RequestInterface $request): void
    {
        $parser = $this->getInstance();

        $result = $parser->parseRequest($targetClass, $request);

        // @todo: SerializerFactory and Seralizer need to be stubbed so that we can assert the parser result.
        // @see: https://loyaltycorp.atlassian.net/browse/PYMT-1222
        /** @noinspection UnnecessaryAssertionInspection Testing concrete implementation passed by data provider */
        self::assertInstanceOf($targetClass, $result);
    }

    /**
     * Tests that the parser returns validation failures when an invalid payload is provided.
     *
     * @dataProvider getInvalidRequestScenarios
     *
     * @param string $targetClass
     * @param \Psr\Http\Message\RequestInterface $request
     * @param string[] $expected
     *
     * @return void
     *
     * @throws \EoneoPay\PhpSdk\Services\Webhooks\Exceptions\InvalidEntityClassException
     * @throws \EoneoPay\PhpSdk\Services\Webhooks\Exceptions\WebhookParserValidationException
     */
    public function testParseRequestValidationFailure(
        string $targetClass,
        RequestInterface $request,
        array $expected
    ): void {
        $parser = $this->getInstance();

        $this->expectException(WebhookParserValidationException::class);
        $this->expectExceptionMessage('The webhook parser failed to validate the parsed entity.');

        try {
            $parser->parseRequest($targetClass, $request, [
                PropertyNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            ]);
        } catch (WebhookParserValidationException $exception) {
            $this->assertValidationExceptionErrors($exception, $expected);

            throw $exception;
        }
    }

    /**
     * Tests that the 'parse' method successfully converts the provided JSON to a typed object.
     *
     * @return void
     *
     * @throws \EoneoPay\PhpSdk\Services\Webhooks\Exceptions\InvalidEntityClassException
     * @throws \EoneoPay\PhpSdk\Services\Webhooks\Exceptions\WebhookParserValidationException
     */
    public function testParseSuccessful(): void
    {
        $parser = $this->getInstance();
        $json = <<<'JSON'
{
    "country": "AU",
    "created_at": "2019-07-31T06:08:07Z",
    "currency": "AUD",
    "customer": {"email": "customer@example.com"},
    "id": "cc0a468f1fb821f457977d8f6b7f3f63",
    "name": "User Name",
    "number": "987654321",
    "one_time": false,
    "pan": "123-456...4321",
    "prefix": "123-456",
    "token": "FDJ9934242YBP3C2ZC43",
    "type": "bank_account",
    "updated_at": "2019-07-31T06:08:07Z"
}
JSON;
        $expected = new BankAccount([
            'country' => 'AU',
            'createdAt' => '2019-07-31T06:08:07Z',
            'currency' => 'AUD',
            'id' => 'cc0a468f1fb821f457977d8f6b7f3f63',
            'name' => 'User Name',
            'number' => '987654321',
            'oneTime' => false,
            'pan' => '123-456...4321',
            'prefix' => '123-456',
            'token' => 'FDJ9934242YBP3C2ZC43',
            'type' => 'bank_account',
            'updatedAt' => '2019-07-31T06:08:07Z',
        ]);

        $result = $parser->parse(BankAccount::class, $json, 'json');

        /** @var \EoneoPay\PhpSdk\Endpoints\PaymentSources\BankAccount $result */
        self::assertInstanceOf(BankAccount::class, $result);
        self::assertEquals($expected, $result);
    }

    /**
     * Tests that the 'parse' method throws an exception when the provided class name is not
     * that of a class which implements EntityInterface.
     *
     * @return void
     *
     * @throws \EoneoPay\PhpSdk\Services\Webhooks\Exceptions\InvalidEntityClassException
     * @throws \EoneoPay\PhpSdk\Services\Webhooks\Exceptions\WebhookParserValidationException
     */
    public function testParseThrowsExceptionOnNonEntityClass(): void
    {
        $this->expectException(InvalidEntityClassException::class);

        $parser = $this->getInstance();

        $parser->parse(\stdClass::class, '{}', 'json');
    }

    /**
     * Gets an instance of the parser.
     *
     * @param \Symfony\Component\Serializer\SerializerInterface|null $serializer
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface|null $validator
     *
     * @return \EoneoPay\PhpSdk\Services\Webhooks\Parser
     */
    private function getInstance(
        ?SerializerInterface $serializer = null,
        ?ValidatorInterface $validator = null
    ): Parser {
        return new Parser(
            $serializer ?? $this->getSerializer(),
            $validator ?? $this->getValidator()
        );
    }
}

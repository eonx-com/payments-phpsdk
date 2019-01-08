<?php
declare(strict_types=1);

namespace Tests\EoneoPay\PhpSdk\Requests\SchedulePayments;

use EoneoPay\PhpSdk\Requests\Payloads\Amount;
use EoneoPay\PhpSdk\Requests\Payloads\Ewallet as EwalletPayload;
use EoneoPay\PhpSdk\Requests\SchedulePayments\Ewallet\CreateRequest;
use EoneoPay\PhpSdk\Requests\SchedulePayments\Ewallet\GetRequest;
use EoneoPay\PhpSdk\Requests\SchedulePayments\RemoveRequest;
use EoneoPay\PhpSdk\Responses\SchedulePayments\Ewallet;
use EoneoPay\Utils\DateTime;
use EoneoPay\Utils\Interfaces\UtcDateTimeInterface;
use Tests\EoneoPay\PhpSdk\Stubs\Endpoints\EwalletRequestStub;
use Tests\EoneoPay\PhpSdk\Stubs\Endpoints\EwalletResponseStub;
use Tests\EoneoPay\PhpSdk\TestCases\RequestTestCase;

/**
 * @noinspection EfferentObjectCouplingInspection High coupling for testing only
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) High coupling for testing only
 *
 * @covers \EoneoPay\PhpSdk\Requests\SchedulePayments\Ewallet\CreateRequest
 * @covers \EoneoPay\PhpSdk\Requests\SchedulePayments\Ewallet\GetRequest
 * @covers \EoneoPay\PhpSdk\Requests\SchedulePayments\RemoveRequest
 * @covers \EoneoPay\PhpSdk\Requests\SchedulePayments\SchedulePaymentRequest
 */
class EwalletRequestTest extends RequestTestCase
{
    /**
     * Test create schedule payment successfully.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\BaseException
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function testCreateSchedulePaymentsSuccessfully(): void
    {
        $request = $this->getSchedulePaymentData();

        $data = \array_merge($request, $this->getEndpointData());

        $response = $this->createClient($data)->create(new CreateRequest(\array_merge(
            $request,
            ['ewallet' => new EwalletRequestStub()]
        )));

        // assertions
        self::assertInstanceOf(Ewallet::class, $response);
        $this->assertSchedulePayment($data, $response);
    }

    /**
     * Test get schedule payment by id successfully.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\BaseException
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function testGetSchedulePaymentsSuccessfully(): void
    {
        $data = \array_merge($this->getSchedulePaymentData(), $this->getEndpointData());

        $response = $this->createClient($data)->get(new GetRequest([
            'id' => $data['id']
        ]));

        // assertions
        self::assertInstanceOf(Ewallet::class, $response);
        $this->assertSchedulePayment($data, $response);
    }

    /**
     * Test list schedule payment successfully.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\BaseException
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    public function testListSchedulePaymentsSuccessfully(): void
    {
        $data = [\array_merge($this->getSchedulePaymentData(), $this->getEndpointData())];

        $response = $this->createClient($data)->list(new GetRequest());

        self::assertGreaterThan(0, \count($response));

        // assertions
        self::assertInstanceOf(Ewallet::class, $response[0]);
        $this->assertSchedulePayment($data[0], $response[0]);
    }

    /**
     * Test remove schedule payment successfully.
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\BaseException
     */
    public function testRemoveSchedulePaymentSuccessfully(): void
    {
        self::assertNull($this->createClient([], 204)->delete(new RemoveRequest([
            'id' => 'ewallet-schedule-payment-id'
        ])));
    }

    /**
     * Assert schedule payment unit rest results.
     *
     * @param mixed[] $data
     * @param \EoneoPay\PhpSdk\Responses\SchedulePayments\Ewallet $response
     *
     * @return void
     */
    private function assertSchedulePayment(array $data, Ewallet $response): void
    {
        /** @var \EoneoPay\PhpSdk\Requests\Payloads\Amount $amount */
        $amount = $data['amount'];

        self::assertInstanceOf(EwalletPayload::class, $response->getEwallet());

        /** @var \EoneoPay\PhpSdk\Requests\Payloads\Ewallet $ewallet */
        $ewallet = $response->getEwallet();
        $this->assertEwallet($data, $ewallet);

        self::assertSame(
            $amount->getTotal() ?? null,
            $response->getAmount() ? $response->getAmount()->getTotal() : null
        );
        self::assertSame(
            $amount->getCurrency() ?? null,
            $response->getAmount() ? $response->getAmount()->getCurrency() : null
        );
        self::assertSame($data['frequency'], $response->getFrequency());
        self::assertSame($data['id'], $response->getId());
    }

    /**
     * Get endpoint response data.
     *
     * @return mixed[]
     */
    private function getEndpointData(): array
    {
        return [
            'ewallet' => (new EwalletResponseStub())->toArray()
        ];
    }

    /**
     * Get schedule payment data.
     *
     * @return mixed[]
     *
     * @throws \EoneoPay\Utils\Exceptions\InvalidDateTimeStringException
     */
    private function getSchedulePaymentData(): array
    {
        return [
            'amount' => new Amount([
                'currency' => 'AUD',
                'total' => '10.00'
            ]),
            'end_date' => null,
            'frequency' => 'weekly',
            'id' => $this->generateId('scp'),
            'start_date' => (new DateTime())->format(UtcDateTimeInterface::FORMAT_ZULU)
        ];
    }
}
<?php
declare(strict_types=1);

namespace EoneoPay\PhpSdk\Exceptions;

use EoneoPay\Utils\Exceptions\RuntimeException as BaseRuntimeException;
use Throwable;

class RuntimeException extends BaseRuntimeException
{
    /**
     * The sub error code.
     *
     * @var int
     */
    protected $subCode;

    /**
     * BaseException constructor.
     *
     * @param string|null $message
     * @param mixed[]|null $messageParameters Parameters for $message
     * @param int|null $code
     * @param \Throwable|null $previous
     * @param int|null $subCode
     */
    public function __construct(
        ?string $message = null,
        ?array $messageParameters = null,
        ?int $code = null,
        ?Throwable $previous = null,
        ?int $subCode = null
    ) {
        parent::__construct($message ?? '', $messageParameters, $code ?? 0, $previous);

        $this->subCode = $subCode ?? 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode(): int
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorSubCode(): int
    {
        return $this->subCode;
    }
}

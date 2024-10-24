<?php declare(strict_types=1);

namespace App\TON;

use Brick\Math\BigNumber;
use App\TON\Interop\Boc\Exceptions\BitStringException;
use App\TON\Interop\Boc\Exceptions\CellException;
use App\TON\Interop\Bytes;
use App\TON\Contracts\Exceptions\ContractException;
use App\TON\Contracts\Interfaces\Deployable;
use App\TON\Contracts\Messages\Exceptions\MessageException;
use App\TON\Contracts\Messages\ExternalMessage;
use App\TON\Contracts\Messages\ExternalMessageOptions;
use App\TON\Contracts\Messages\InternalMessage;
use App\TON\Contracts\Messages\InternalMessageOptions;
use App\TON\Contracts\Messages\MessageData;
use App\TON\Contracts\Wallets\Exceptions\WalletException;
use App\TON\Exceptions\DeployerException;
use App\TON\Exceptions\TransportException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Deployer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        Transport $transport
    ) {}

    /**
     * @throws DeployerException|TransportException
     */
    public function deploy(DeployOptions $options, Deployable $deployable): void
    {
        if ($this->transport->getState($deployable->getAddress()) === AddressState::ACTIVE) {
            if (!empty($this->logger)) {
                $this->logger->warning(sprintf(
                    "Contract %s already deployed",
                    $deployable->getAddress()->toString(true, true, false),
                ));
            }
            return;
        }

        $this->validateStateInit($deployable);

        try {
            if (!empty($this->logger)) {
                $this->logger->debug("External message construction for deploy ");
            }
            $externalMessage = $this->createExternal(
                $options,
                $deployable,
                (int)$options->deployerWallet->seqno($this->transport),
            );
        } catch (MessageException | ContractException | BitStringException $e) {
            if (!empty($this->logger)) {
                $this->logger->error(
                        sprintf("External message construction error: %s", $e->getMessage()),
                        [
                            "exception" => $e,
                            "deployable" => '',
                        ]
                    );
            }


            throw new DeployerException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        try {
            $this
                ->transport
                ->sendMessage(
                    $externalMessage,
                    $options->deployerSecretKey,
                );
            $this
                ->logger
                ->debug(
                    sprintf(
                        "Smart contract deployed to %s",
                        $deployable->getAddress()->toString(true, true, true),
                    ),
                );
        } catch (TransportException $e) {
            if (!empty($this->logger)) {
                $this->logger->error(
                    sprintf("External message sending error: %s", $e->getMessage()),
                    [
                        "exception" => $e,
                        "deployable" => json_encode($deployable),
                    ]
                );
            }

            throw new DeployerException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Returns estimated deploy transaction fees.
     *
     * @throws DeployerException
     */
    public function estimateFee(DeployOptions $options, Deployable $deployable): BigNumber
    {
        // @TODO: It is probably worth rewriting for manual fees calculation

        $this->validateStateInit($deployable);

        try {
            return $this
                ->transport
                ->estimateFee(
                    $deployable->getAddress(),
                    Bytes::bytesToBase64(
                        $this
                            ->createExternal($options, $deployable, 0)
                            ->sign($options->deployerSecretKey)
                            ->toBoc( false),
                    ),
                );
        } catch (CellException | TransportException | BitStringException | WalletException | MessageException | ContractException $e) {
            if (!empty($this->logger)) {
                $this->logger->error(
                    "Deploy fee calculation error: " . $e->getMessage(),
                    [
                        "exception" => $e,
                        "deployable" => json_encode($deployable),
                    ]
                );
            }
            throw new DeployerException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @throws BitStringException
     * @throws ContractException
     * @throws WalletException
     * @throws MessageException
     */
    private function createExternal(DeployOptions $options, Deployable $deployable, int $seqno): ExternalMessage
    {
        $deployableAddress = $deployable->getAddress();
        $deployerAddress = $options->deployerWallet->getAddress();
        $internal = new InternalMessage(
            new InternalMessageOptions(
               false,
                $deployableAddress,
                $options->storageAmount,
               $deployerAddress,
            ),
            new MessageData(
                null,
                $deployable->getStateInit()->cell(),
            )
        );

        $sm = $options->deployerWallet->createSigningMessage($seqno);
        $sm
            ->bits
            ->writeUint8(SendMode::PAY_GAS_SEPARATELY);
        $sm->refs[] = $internal->cell();

        return new ExternalMessage(
            new ExternalMessageOptions(
                null,
                $deployerAddress,
            ),
            new MessageData(
                $sm,
                $seqno === 0
                    ? $options->deployerWallet->getStateInit()->cell()
                    : null,
            )
        );
    }

    /**
     * @throws DeployerException
     */
    private function validateStateInit(Deployable $deployable)
    {
        $stateInit = $deployable->getStateInit();
        $initCode = $stateInit->code;
        $initData = $stateInit->data;


        if (is_null($initCode)) {
            throw new DeployerException("Empty init code");
        }

        if (is_null($initData)) {
            throw new DeployerException("Empty init data");
        }

    }
}

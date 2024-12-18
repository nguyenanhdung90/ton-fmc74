<?php declare(strict_types=1);

namespace App\TON;

use App\TON\Interop\Address;
use App\TON\Exceptions\AwaiterMaxTimeException;
use App\TON\Exceptions\TransportException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ContractAwaiter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        Transport $transport,
    ) {}

    /**
     * @throws AwaiterMaxTimeException
     */
    public function waitForActive(Address $address, int $tickTimeout = 5, int $maxWait = 600): void
    {
        $startTime = time();
        $maxTime = $startTime + $maxWait;

        /**
         * @return AddressState|null
         * @throws AwaiterMaxTimeException
         */
        $getState = function () use ($address, $tickTimeout, $maxTime): ?AddressState {
            if (time() >= $maxTime) {
                throw new AwaiterMaxTimeException(sprintf(
                    "Max wait time reached for address: %s",
                    $address->toString(true, true, false),
                ));
            }

            sleep($tickTimeout);

            try {
                return $this->transport->getState($address);
            } catch (TransportException $e) {
                $this
                    ->logger
                    ?->debug("Transport error: " . $e->getMessage(), [
                        "exception" => $e,
                    ]);
            }

            return null;
        };

        do {
            $state = $getState();
        } while ($state !== AddressState::ACTIVE);
    }
}

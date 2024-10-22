<?php declare(strict_types=1);

namespace App\TON\Transports\Toncenter;

use App\TON\Transports\Toncenter\Exceptions\ClientException;
use App\TON\Transports\Toncenter\Exceptions\TimeoutException;
use App\TON\Transports\Toncenter\Exceptions\ValidationException;
use App\TON\Transports\Toncenter\Models\JsonRpcResponse;
use App\TON\Transports\Toncenter\Models\TonResponse;
use App\TON\Interop\Address;

/**
 * Toncenter API client
 */
interface ToncenterV2Client
{
    /**
     * Run get method on smart contract.
     *
     * @param Address $address Smart contract address
     * @param string $method Method name
     * @param string[][] $stack Stack array
     * @throws \App\TON\Transports\Toncenter\Exceptions\ValidationException
     * @throws \App\TON\Transports\Toncenter\Exceptions\TimeoutException
     * @throws \App\TON\Transports\Toncenter\Exceptions\ClientException
     * @link https://toncenter.com/api/v2/#/run%20method/run_get_method_runGetMethod_post
     *
     */
    public function runGetMethod(Address $address, string $method, array $stack = []);

    /**
     * Send serialized boc file: fully packed and serialized external message to blockchain.
     *
     * @link https://toncenter.com/api/v2/#/send/send_boc_sendBoc_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendBoc($boc): TonResponse;

    /**
     * Send serialized boc file: fully packed and serialized external message to blockchain. The method returns message hash.
     *
     * @link https://toncenter.com/api/v2/#/send/send_boc_return_hash_sendBocReturnHash_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendBocReturnHash($boc): TonResponse;

    /**
     * Send query - unpacked external message.
     *
     * This method takes address, body and init-params (if any), packs it to external message and sends to network.
     * All params should be boc-serialized.
     *
     * @param array{addres: string, body: string, init_code?: string, init_data?: string} $body
     * @link https://toncenter.com/api/v2/#/send/send_query_sendQuery_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function sendQuery(array $body): TonResponse;



    /**
     * All methods in the API are available through JSON-RPC protocol.
     *
     * @param array{method: string, params: array} $params
     * @return JsonRpcResponse
     * @link https://toncenter.com/api/v2/#/json%20rpc/jsonrpc_handler_jsonRPC_post
     *
     * @throws ValidationException
     * @throws TimeoutException
     * @throws ClientException
     */
    public function jsonRPC(array $params): JsonRpcResponse;
}

<?php
/**
 * Created by PhpStorm.
 * User: mmammar
 * Date: 10/27/18
 * Time: 3:55 PM
 */

namespace App\System\PaymentGateway;


use App\System\PaymentGateway\Models\GateWay;

interface ParentGatewayInterface {

    /**
     * @param string $backend_name
     * @param array $credentials
     * @return mixed
     */
    public function listAllGateways(string $backend_name, array $credentials);

    /**
     * @param string $backend_name
     * @param array $credentials
     * @param GateWay $gateway Stored in db as json
     * @return array|null
     */
    public function addGatewayOnParentServer(string $backend_name, array $credentials, GateWay $gateway);

    /**
     * @param string $backend_name
     * @param array $credentials
     * @param GateWay $gateway Stored in db as json
     * @return array|null
     */
    public function removeGatewayOnParentServer(string $backend_name, array $credentials, GateWay $gateway);

    /**
     * @param string $backend_name
     * @param array $credentials
     * @param GateWay $gateway Stored in db as json
     * @return GateWay|null
     */
    public function updateGatewayOnParentServer(string $backend_name, array $credentials, GateWay $gateway);

    /**
     * @param string $backend_name
     * @param array $credentials
     * @return mixed
     */
    public function getAddedGateways(string $backend_name, array $credentials);

}
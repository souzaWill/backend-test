<?php

namespace App\Integrations\Banking\Account;

use App\Integrations\Banking\Gateway;
use App\Repositories\Account\FindByUser;
use App\Exceptions\InternalErrorException;

class Find extends Gateway
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Id externo da conta
     *
     * @var string
     */
    protected string $externalId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Busca os dados de conta
     *
     * @return void
     */
    protected function findAccountData(): void
    {
        $account = (new FindByUser($this->userId))->handle();

        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001 //TODO: criar constante para esse codigo de erro ou erro persoalizado
            );
        }

        //TODO: o nome dessa função esta errado, pois oque ela faz de verdade e setar o externalId
        //tambem moveria a busca do external_id para app/UseCases/Account/Show.php, e passaria via injecao de dependencia
        $this->externalId = $account['external_id'];
    }

    /**
     * Constroi a url da request
     *
     * @return string
     */
    protected function requestUrl(): string
    {
        return "accounts/$this->externalId";
    }

    /**
     * Modifica o status de uma conta
     *
     * @return array
     */
    public function handle(): array
    {
        $this->findAccountData();
        $url = $this->requestUrl();

        //TODO: o mais adequado para o nome da variavel seria $response
        $request = $this->sendRequest(
            method: 'get',
            url:    $url,
            action: 'FIND_ACCOUNT',
            params: []
        );

        return $this->formatDetailsResponse($request);
    }
}

<?php

namespace App\Domains\Card;

use App\Domains\BaseDomain;
use App\Repositories\Account\FindByUser;
use App\Exceptions\InternalErrorException;
use App\Repositories\Card\CanUseExternalId;

class Register extends BaseDomain
{
    /**
     * Id da conta
     *
     * @var string
     */
    protected string $accountId;

    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Id do cartão
     *
     * @var string
     */
    protected string $cardId;

    /**
     * PIN do cartão
     *
     * @var string
     */
    protected string $pin;

    public function __construct(string $userId, string $pin, string $cardId)
    {
        $this->userId = $userId;
        $this->pin    = $pin;
        $this->cardId = $cardId;
    }

    /**
     * Busca o id de conta
     *
     * @return void
     */
    protected function findAccountId(): void // TODO: mudar para metodo privado
    {
        // TODO: usar injeção de dependência ao invés de instanciar diretamente
        $account = (new FindByUser($this->userId))->handle();

        if (is_null($account)) {
            // TODO: Criar uma exception específica para conta não encontrada
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001 // usar constante ou melhor ainda seria um uma exception específica ex: AccountNotFoundException ja definindo o codigo
            );
        }

        $this->accountId = $account['id'];
    }

    /**
     * Cartão não pode já estar vinculado
     */
    protected function checkExternalId(): void // TODO: mudar para metodo privado
    {
        // TODO: usar injeção de dependência ao invés de instanciar diretamente
        if (!(new CanUseExternalId($this->cardId))->handle()) {
            // TODO: Criar uma exception específica para cartão já vinculado
            throw new InternalErrorException(
                'Não é possível vincular esse cartão',
                0
            );
        }
    }

    /**
     * Checa se é possível vincular o cartão
     *
     * @return self
     */
    public function handle(): self
    {
        $this->findAccountId();
        $this->checkExternalId();

        return $this;
    }
}

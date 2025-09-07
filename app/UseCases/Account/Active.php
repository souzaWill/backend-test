<?php

namespace App\UseCases\Account;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Repositories\Account\UpdateStatus as RepositoryUpdateStatus;
use App\Integrations\Banking\Account\UpdateStatus as IntegrationUpdateStatus;

class Active extends BaseUseCase
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Conta
     *
     * @var array
     */
    protected array $account;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Atualiza no banco de dados
     *
     * @return void
     */
    protected function updateDatabase(): void
    {
        //TODO: talvez seria criar um dominio para account, assim, o domain orquestraria a repository e a integração, seria o jeito mais DDD
        // porem existem trade-offs nisso, pois aqui no usecase manteria uma certa simplicidade
        (new RepositoryUpdateStatus($this->userId, 'active'))->handle();
    }

    /**
     * Atualiza a conta
     *
     * @return void
     */
    protected function updateStatus(): void
    {
        $this->account = (new IntegrationUpdateStatus($this->userId, 'active'))->handle();
    }

    /**
     * Ativa a conta
     */
    public function handle(): void
    {
        try {
            //TODO: para garantir a integridade, primeiro atualizar no externo e depois no banco, caso falhe no externo nao atualiza no banco, caso falhe no banco ja atualizou no externo
            //e talvez usar transacoes, para isso uma TransactionInterface que implemente begin, commit, rollback sem expor o banco de dados e uma infra/Database/TransactionService,
            // que implemente essas funcoes
            $this->updateDatabase();
            $this->updateStatus();
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'userId' => $this->userId,
                ]
            );
        }
    }
}

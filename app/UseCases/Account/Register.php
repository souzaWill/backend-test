<?php

namespace App\UseCases\Account;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Repositories\User\Find;
use App\Repositories\Account\Create;
use App\Exceptions\InternalErrorException;
use App\Integrations\Banking\Account\Create as BankingCreate;

class Register extends BaseUseCase
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Id da empresa
     *
     * @var string
     */
    protected string $companyId;

    /**
     * Usuário
     *
     * @var array
     */
    protected array $user;

    /**
     * Conta
     *
     * @var array
     */
    protected array $account;

    public function __construct(string $userId, string $companyId)
    {
        $this->userId    = $userId;
        $this->companyId = $companyId;
    }

    /**
     * Encontra o usuário
     *
     * @return void
     */
    protected function findUser(): void
    {
        // TODO: usar injeção de dependência ao invés de instanciar diretamente
        $user = (new Find($this->userId, $this->companyId))->handle();
        if (is_null($user)) {
            // TODO: Criar uma exception específica para usuário não encontrado
            throw new InternalErrorException(
                'USER_NOT_FOUND',
                146001001 // usar constante ou melhor ainda seria um uma exception específica ex: UserNotFoundException ja definindo o codigo
            );
        }

        $this->user = $user;
    }

    /**
     * Cria a conta
     *
     * @return void
     */
    protected function register(): void
    {
        $this->account = (new BankingCreate(
            $this->user['name'],
            $this->user['document_number'],
            $this->user['email']
        ))->handle();
    }

    /**
     * Registra no banco de dados
     *
     * @return void
     */
    protected function store(): void
    {
        (new Create($this->userId, $this->account['data']['id']))->handle();
    }

    /**
     * Cria a conta
     */
    public function handle(): void
    {
        try {
            //TODO: usar transações para garantir a integridade, caso falhe em algum ponto, desfazer o que ja foi feito
            $this->findUser();
            $this->register();
            $this->store();
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

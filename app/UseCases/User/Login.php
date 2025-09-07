<?php

namespace App\UseCases\User;

use Throwable;
use App\UseCases\BaseUseCase;
//TODO: nomear create_token para CreateToken pois não segue o padrão PascalCase
// porem nao tem a necessidade do alias
// ao inves de chamar a repositorio diretamente aqui, criar um useCase App\UseCases\Token\CreateToken que utilize esse repositorio
use App\Repositories\Token\Create as create_token;

class Login extends BaseUseCase
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * Token de acesso
     *
     * @var string
     */
    protected string $token;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * Criação de token de acesso
     *
     * @return void
     */
    protected function createToken(): void
    {
        $this->token = (new create_token($this->id))->handle();
    }

    /**
     * Cria um usuário MANAGER e a empresa
     */
    public function handle()
    {
        try {
            $this->createToken();
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'id' => $this->id,
                ]
            );
        }

        return [
            'token' => $this->token,
        ];
    }
}

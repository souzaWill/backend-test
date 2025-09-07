<?php

namespace App\Repositories\Token;

use App\Models\User;

//TODO: criar interface TokenRepositoryInterface e implementar aqui, assim facilitando testes unitarios e possivel troca de implementacao
// Criar um useCase App\UseCases\Token\CreateToken que utilize esse repositorio
//TODO: avaliar comentario
class Create
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $id;

    /**
     * Permissões
     *
     * @var array
     */
    protected array $permissions;

    /**
     * Model base para implementação
     *
     * @var string
     */
    protected string $model;

    public function __construct(string $id, array $permissions = [])
    {
        $this->id          = $id;
        $this->permissions = $permissions;
        //TODO: aqui fugiu do padrão, de definir a model em um método setModel()
        $this->model       = User::class;
    }

    /**
     * Criação de token de acesso do usuário
     *
     * @return string
     */
    public function handle(): string
    {
        //TODO: aqui esta fugindo do padrão de usar $this->builder, assim o acoplamento entre a repository e o eloquent fica maior ainda
        // com isso essa repository depende diretamente do eloquent, tanto que as funcoes de createToken e plainTextToken são do eloquent
        return app($this->model)
            ->findOrFail($this->id)
            ->createToken(config('auth.token_name'), $this->permissions)
            ->plainTextToken;
    }
}

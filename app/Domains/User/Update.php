<?php

namespace App\Domains\User;

use App\Domains\BaseDomain;
use Illuminate\Support\Facades\Hash;
use App\Repositories\User\CanUseEmail;
use App\Exceptions\InternalErrorException;

class Update extends BaseDomain
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $id;

    /**
     * Empresa
     *
     * @var string
     */
    protected string $companyId;

    /**
     * Nome
     *
     * @var string|null
     */
    protected ?string $name;

    /**
     * Email
     *
     * @var string|null
     */
    protected ?string $email;

    /**
     * Senha
     *
     * @var string|null
     */
    protected ?string $password;

    /**
     * Tipo
     *
     * @var string|null
     */
    protected ?string $type;

    public function __construct(
        string $id,
        string $companyId,
        ?string $name,
        ?string $email,
        ?string $password,
        ?string $type
    ) {
        $this->id        = $id;
        $this->companyId = $companyId;
        $this->name      = $name;
        $this->email     = $email;
        $this->type      = $type;

        $this->cryptPassword($password);
    }

    /**
     * Encripta a senha
     *
     * @param string|null $password
     *
     * @return void
     */
    protected function cryptPassword(?string $password): void
    {
        // TODO: remover a função de hash direta, usar injeção de dependência ex: bind PasswordHasherInterface -> LaravelPasswordHasher 
        $this->password = !is_null($password) ? Hash::make($password) : null;
    }

    /**
     * Email deve ser únicos no sistema
     *
     * @return void
     */
    protected function checkEmail(): voidxw
    {
        if (is_null($this->email)) {
            return;
        }
        if (!(new CanUseEmail($this->email))->handle()) {
            throw new InternalErrorException(
                'Não é possível adicionar o E-mail informado',
                0
            );
        }
    }

    /**
     * Valida o tipo
     *
     * @return void
     */
    protected function checkType(): void
    {
        if (is_null($this->type)) {
            return;
        }
        // TODO: codigo duplicado, existe a mesma validacao em User/Create.php, considerar o uso de ValueObjects ou um Enum
        if (!in_array($this->type, ['USER', 'VIRTUAL', 'MANAGER'])) {
            throw new InternalErrorException(
                'Não é possível adicionar o tipo informado',
                0
            );
        }
    }

    /**
     * Checa se é possível realizar a criação do usuário
     *
     * @return self
     */
    public function handle(): self
    {
        $this->checkEmail();
        $this->checkType();

        return $this;
    }
}

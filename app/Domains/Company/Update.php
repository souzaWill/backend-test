<?php

namespace App\Domains\Company;

use App\Domains\BaseDomain;

class Update extends BaseDomain
{
    /**
     * Id da empresa
     *
     * @var string
     */
    protected string $id;

    /**
     * Nome
     *
     * @var string
     */
    protected string $name;

    public function __construct(string $id, string $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }

    /**
     * Checa se é possível modificar a empresa
     *
     * @return self
     */
    public function handle(): self
    {
        // Nenhuma validação necessária
        // TODO: classe sem efeito, remover ou implementar lógica

        return $this;
    }
}

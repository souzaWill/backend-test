<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;
use App\Domains\User\Update as UpdateDomain;

class Update extends BaseRepository
{
    /**
     * Parâmetros de criação de usuário
     *
     * @var UpdateDomain
     */
    protected UpdateDomain $domain;

    /**
     * Setar a model do usuário
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = User::class;
    }

    public function __construct(UpdateDomain $domain)
    {
        $this->domain = $domain;

        parent::__construct();
    }

    /**
     * Modificação de usuário
     *
     * @return array
     */
    public function handle(): array
    {
        //TODO: usar o $this->builder o acoplamento entre a repository o eloquento fica maior ainda
        // assim dificulta a troca de implementação, testabilidade, etc... 
        // como solução recomendaria uma abstração dessa funcão na BaseRepository
        $this->builder->where('company_id', $this->domain->companyId);

        return $this->update(
            $this->domain->id,
            array_filter(
                [
                    'name'     => $this->domain->name,
                    'email'    => $this->domain->email,
                    'password' => $this->domain->password,
                    'type'     => $this->domain->type,
                ]
            )
        );
    }
}

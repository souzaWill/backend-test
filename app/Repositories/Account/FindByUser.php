<?php

namespace App\Repositories\Account;

use App\Models\Account;
use App\Repositories\BaseRepository;

class FindByUser extends BaseRepository
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Setar a model do usuário
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = Account::class;
    }

    public function __construct(string $userId)
    {
        $this->userId = $userId;

        parent::__construct();
    }

    /**
     * Enconta a conta
     *
     * @return array|null
     */
    public function handle(): ?array
    {
        //TODO: usar o $this->builder o acoplamento entre a repository o eloquento fica maior ainda
        // assim dificulta a troca de implementação, testabilidade, etc... 
        // como solução recomendaria uma abstração dessa funcão na BaseRepository, tipo um findBy()
        $this->builder->where('user_id', $this->userId);

        return $this->first();
    }
}

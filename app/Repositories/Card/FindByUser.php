<?php

namespace App\Repositories\Card;

use App\Models\Card;
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
     * Setar a model do cartão
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = Card::class;
    }

    public function __construct(string $userId)
    {
        $this->userId = $userId;

        parent::__construct();
    }

    //TODO: essa função nao está sendo chamada em lugar nenhum
    /**
     * Join com accounts
     *
     * @return void
     */
    protected function joinAccount(): void
    {
        $this->builder->leftJoin(
            'accounts',
            'accounts.id',
            '=',
            'cards.account_id'
        );
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
        // como solução recomendaria uma abstração dessa funcão na BaseRepository
        $this->builder->where('accounts.user_id', $this->userId);
        //TODO: faltou chamar o joinAccount aqui

        return $this->first(['cards.*']);
    }
}

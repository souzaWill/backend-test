<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class Retrieve extends BaseRepository
{
    /**
     * Id da empresa
     *
     * @var string
     */
    protected string $companyId;

    /**
     * Name
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
     * Status
     *
     * @var string|null
     */
    protected ?string $status;

    /**
     * Setar a model do usuário
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = User::class;
    }

    public function __construct(string $companyId, ?string $name, ?string $email, ?string $status)
    {
        $this->companyId = $companyId;
        $this->name      = $name;
        $this->email     = $email;
        $this->status    = $status;

        parent::__construct();
    }

    /**
     * Left join com accounts
     *
     * @return void
     */
    protected function leftJoinAccount(): void
    {
        $this->builder->leftJoin(
            'accounts',
            'accounts.user_id',
            '=',
            'users.id'
        );
    }

    /**
     * Lista de usuários (Paginado)
     *
     * @return LengthAwarePaginator
     */
    public function handle(): LengthAwarePaginator
    {
        //TODO: evitar o whereRaw, o eloquent tem suporte a like nativamente, porem seria melhor abstrair isso na BaseRepository
        $this->leftJoinAccount();

        //TODO: extrair esses filtros para uma função privada para melhorar a legibilidade
        if ($this->name) {
            $this->builder->whereRaw("name LIKE '%" . $this->name . "%'");
        }

        if ($this->email) {
            $this->builder->whereRaw("email LIKE '%" . $this->email . "%'");
        }

        if ($this->status) {
            //TODO: INACTIVE deveria ser uma constante ou um enum
            if ($this->status === 'INACTIVE') {
                $this->builder->whereRaw('accounts.id IS NULL');
            } else {
                $this->builder->whereRaw('accounts.status = "' . $this->status . '"');
            }
        }

        //TODO: usar o $this->builder o acoplamento entre a repository o eloquento fica maior ainda
        // assim dificulta a troca de implementação, testabilidade, etc... 
        // como solução recomendaria uma abstração dessa funcão na BaseRepository
        $this->builder->where('company_id', $this->companyId)
            ->orderBy('name');

        return $this->paginate(['users.*']);
    }
}

<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;

class CanUseEmail extends BaseRepository
{
    /**
     * Email
     *
     * @var string
     */
    protected string $email;

    /**
     * Setar a model do usuário
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = User::class;
    }

    public function __construct(string $email)
    {
        $this->email = $email;

        parent::__construct();
    }

    /**
     * Valida se o email é único
     *
     * @return bool
     */
    public function handle(): bool
    {
        $user = $this->builder
            ->where('email', $this->email)
            ->first();
            //TODO: usar exists, que é mais performático pois nao precisa trazer o registro completo
            // TODO: porem o ideal seria a abstração disso na BaseRepository

        return is_null($user);
    }
}

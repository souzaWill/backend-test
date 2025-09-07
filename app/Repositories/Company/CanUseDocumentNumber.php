<?php

namespace App\Repositories\Company;

use App\Models\Company;
use App\Repositories\BaseRepository;

class CanUseDocumentNumber extends BaseRepository
{
    /**
     * CNPJ
     *
     * @var string
     */
    protected string $documentNumber;

    /**
     * Setar a model da empresa
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = Company::class;
    }

    public function __construct(string $documentNumber)
    {
        $this->documentNumber = $documentNumber;

        parent::__construct();
    }

    /**
     * Valida se o documento é único
     *
     * @return bool
     */
    public function handle(): bool
    {
        $user = $this->builder
            ->where('document_number', $this->documentNumber)
            ->first();  //TODO: usar exists, que é mais performático pois nao precisa trazer o registro completo
            // TODO: porem o ideal seria a abstração disso na BaseRepository

        return is_null($user);
    }
}

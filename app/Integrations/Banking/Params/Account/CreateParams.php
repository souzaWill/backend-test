<?php

namespace App\Integrations\Banking\Params\Account;

//talvez devia importar BaseParams e extender a classe
class CreateParams
{
    /**
     * ID do Produto
     *
     * @var string
     */
    private string $productId;

    /**
     * Tipo de Conta (prepaid ou postpaid)
     *
     * @var string
     */
    private string $accountType;

    /**
     * Nome
     *
     * @var string
     */
    private string $name;

    /**
     * CPF
     *
     * @var string
     */
    private string $documentNumber;

    /**
     * Email
     *
     * @var string
     */
    private string $email;

    public function __construct(
        string $productId,
        string $accountType,
        string $name,
        string $documentNumber,
        string $email,
    ) {
        $this->productId      = $productId;
        $this->accountType    = $accountType;
        $this->name           = $name;
        $this->documentNumber = $documentNumber;
        $this->email          = $email;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        //TODO: função não utilizada, talvez remover a classe
        return [
            'product_id'      => $this->productId,
            'account_type'    => $this->accountType,
            'name'            => $this->name,
            'document_number' => $this->documentNumber,
            'email'           => $this->email,
        ];
    }
}

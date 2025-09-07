<?php

namespace App\UseCases\Card;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Repositories\Card\Create;
use App\Domains\Card\Register as RegisterDomain;
use App\Integrations\Banking\Card\Register as LinkCard;

class Register extends BaseUseCase
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Id do cartão
     *
     * @var string
     */
    protected string $cardId;

    /**
     * PIN do cartão
     *
     * @var string
     */
    protected string $pin;

    /**
     * Cartão
     *
     * @var array
     */
    protected array $card;

    public function __construct(string $userId, string $pin, string $cardId)
    {
        $this->userId = $userId;
        $this->pin    = $pin;
        $this->cardId = $cardId;
    }

    /**
     * Checa se é possível registrar
     *
     * @return RegisterDomain
     */
    protected function checkIfCanRegister(): RegisterDomain
    {
        return (new RegisterDomain($this->userId, $this->pin, $this->cardId))->handle();
    }

    /**
     * Registra o cartão
     *
     * @return void
     */
    protected function register(RegisterDomain $domain): void
    {
        $this->card = (new LinkCard($domain))->handle();
    }

    /**
     * Registra no banco de dados
     *
     * @return void
     */
    protected function store(RegisterDomain $domain): void
    {
        (new Create($domain))->handle();
    }

    /**
     * Registra o cartão
     */
    public function handle(): array
    {
        try {
            $domain = $this->checkIfCanRegister();
            $this->register($domain);
            $this->store($domain);
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'userId' => $this->userId,
                    'pin'    => $this->pin, //TODO: remover pin dos logs, é um dado sensível
                    'cardId' => $this->cardId,
                ]
            );
        }

        return $this->card;
    }
}

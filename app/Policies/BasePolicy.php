<?php

namespace App\Policies;

use App\Traits\Logger;
use App\Exceptions\PolicyException;
use Illuminate\Support\Facades\Auth;

//TODO: essa classe mistura a verificação de autorização com logging, talvez fosse melhor separar essas responsabilidades
//usar Gate::define('manager-or-owner') para a logica de permissão, e nessa classe Gate::denies('manager-or-owner', $ownerResourceId), caso positivo chamaria a deny
class BasePolicy
{
    use Logger;

    /**
     * Lançamento da exception
     * Esse método não apenas lança a exception com os dados informados,
     * mas também gera um log que poderá ser analizado posteriormente
     *
     * @param string      $message
     * @param int         $code
     * @param string|null $entityId
     * @param string|null $entity
     *
     * @return void
     */
    protected function deny(
        string $message,
        int $code,
        string $entityId = null,
        string $entity = null
    ): void {
        $className = get_called_class();

        $trace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $method = $trace[count($trace) - 1]['function'];

        $this->createLog(
            $message,
            'POLICY_EXCEPTION_ERROR',
            [
                'class_name'     => $className,
                'method'         => $method,
                'message'        => $message,
                'exception_code' => $code,
            ],
            null,
            null,
            null,
            $entityId,
            $entity
        );

        throw new PolicyException($message, $code);
    }

    /**
     * Verifica se o id informado é o do usuário logado
     *
     * @param string $ownerResourceId
     *
     * @return void
     */
    protected function isOwnerResource(string $ownerResourceId): void
    {
        if ($ownerResourceId !== Auth::id()) {
            $this->deny(
                'UNAUTHORIZED',
                146001003,
                Auth::id(),
                'USER'
            );
        }
    }

    /**
     * Verifica se o usuário logado é gestor de contas
     *
     * @return void
     */
    protected function isManagerAccountsUser(): void
    {
        $user = Auth::user();
        if (!is_null($user) && $user->type !== 'MANAGER') {
            $this->deny(
                'UNAUTHORIZED',
                146001003,
                Auth::id(),
                'USER'
            );
        }
    }

    /**
     * Verifica se o usuário dado é do usuário logado, ou se ele
     * é gestor
     *
     * @return void
     */
    protected function isManagerOrOwnerResource(string $ownerResourceId): void
    {
        $user = Auth::user();

        //TODO: ifs anihilados deixam a logica mas complexa do que ela realmente é, um early return deixaria mais claro
        if ($ownerResourceId !== $user->id) {
            if (!is_null($user) && $user->type !== 'MANAGER') {
                $this->deny(
                    'UNAUTHORIZED',
                    146001003,
                    Auth::id(),
                    'USER'
                );
            }
        }
    }
}

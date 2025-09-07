<?php

namespace App\Http\Middleware\App;

use Closure;
use App\Traits\ResponseHelpers;

//TODO: acho que o laravel ja tem algo nativo que faz isso, reinventar a roda as vezes não é a melhor ideia, mas depende de entender as reais necessidades
//TODO: ->middleware('can:view,user'); ou a cada controller usar $this->authorizeResource(User::class, 'user'); no construtor

/**
 * Middleware para validar políticas de acesso aos recursos,
 *
 * Sua finalidade é fazer o autoload das regras de políticas de
 * acesso escritas para o método que está sendo acessado quando
 * a request é enviada para o nosso endpoint
 */
class ResourcePolicies
{
    use ResponseHelpers;

    private const POLICIES_NAMESPACE = 'App\Policies\App';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->applyPolicy();

        return $next($request);
    }

    /**
     * Aplica a validação da política, encontrando a classe
     * de política corresondente com a controller e o método da
     * classe politica também correspondente com o método da controller
     *
     * @return void
     */
    public function applyPolicy()
    {
        $action = explode('\\', request()->route()->getActionName());
        $parameters = array_values(request()->route()->parameters);
        $parameters[] = request()->all();

        $resource = explode('@', end($action))[0];
        $resource = str_replace('Controller', '', $resource);

        $method = explode('@', end($action))[1];
        
        //TODO: erros nao tratados caso a classe ou o metodo nao existam, hoje seu código vai lançar um Error genérico (Call to undefined method).
        //TODO: conferir se deixo esse comentario
        app(self::POLICIES_NAMESPACE . '\\' . $resource)->{$method}(...$parameters);
    }
}

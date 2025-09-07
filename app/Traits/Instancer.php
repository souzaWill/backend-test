<?php

namespace App\Traits;

trait Instancer
{
    //TODO: o laravel nativamente já faz isso com o app()->make() ou resolve()
     // seria interessante usar o container de injeção de dependência do laravel
     // para resolver as dependências automaticamente, ao invés de instanciar diretamente as classes
     // isso melhora a testabilidade, flexibilidade e desacoplamento do código
    /**
     * Método para criar uma nova instância de uma classe
     *
     * @param string $className
     * @param mixed ...$parameters
     *
     * @return mixed
     */
    public function instance(string $className, ...$parameters): mixed
    {
        return new $className(...$parameters);
    }
}

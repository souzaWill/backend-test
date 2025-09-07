<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\UseCases\Company\Show;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\DefaultResponse;
use App\Http\Requests\Company\UpdateRequest;
use App\Http\Resources\Company\ShowResource;
use App\Http\Resources\Company\UpdateResource;
use App\Domains\Company\Update as UpdateDomain;
use App\Repositories\Company\Update as CompanyUpdate;

class CompanyController extends Controller
{
    /**
     * Endpoint de dados de empresa
     *
     * GET api/company
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $response = (new Show(Auth::user()->company_id))->handle();

        return $this->response(
            new DefaultResponse(
                new ShowResource($response)
            )
        );
    }

    /**
     * Endpoint de modificação de empresa
     *
     * PATCH api/company
     *
     * @return JsonResponse
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        //TODO: variaveis fugindo do padrao em ingles utilizado no projeto
        //TODO: chamando o dominio direto na controller, fugindo do padrao de usar o usecase
        $dominio = (new UpdateDomain(
            Auth::user()->company_id,
            $request->name,
        ))->handle();

        // TODO: chamando o repositorio direto na controller, fugindo do padrao, nesse caso aqui deveria ser
        // useCases/Company/Update (useCase) -> Domains\Company\Update (Domain) -> Repositories\Company\Update 
        // e a função useCases/Company/Update->handle() deveria retornar o company atualizado
        // assim evitando a necessidade de fazer uma nova consulta no banco que ocorre na linha 61 
        (new CompanyUpdate($dominio))->handle();

        $resposta = Company::find(Auth::user()->company_id)->first()->toArray();

        return $this->response(
            new DefaultResponse(
                new UpdateResource($resposta)
            )
        );
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UseCases\Card\Register;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Responses\DefaultResponse;
use App\Integrations\Banking\Card\Find;

class CardController extends Controller
{
    // TODO: no phpdoc, esta descrevendo que o metodo e Post, quando na realidade e GET
    /**
     * Exibe dados de um cartão
     *
     * POST api/users/{id}/card
     *
     * @return JsonResponse
     */
    public function show(string $userId): JsonResponse
    {
        //TODO: chamada direta a camada de integracao, foge do padrao de useCases
        $response = (new Find($userId))->handle();

        return $this->response(
            new DefaultResponse($response['data'])
        );
    }

    // TODO: aparentemente este metodo registra um cartao, mas o phpdoc diz que ativa um cartao
    /**
     * Ativa um cartão
     *
     * POST api/users/{id}/card
     *
     * @return JsonResponse
     */
    public function register(string $userId, Request $request): JsonResponse
    {
        //TODO: usar validation form request para validar os dados de entrada, por exemplo se o pin e card_id foram enviados pois são como string, caso contrario da erro
        
        //TODO: criar um UseCases/Params/Card/RegisterParams para evitar passar varios parametros soltos
        $response = (new Register($userId, $request->pin, $request->card_id))->handle();

        return $this->response(
            new DefaultResponse($response['data'])
        );
    }
}

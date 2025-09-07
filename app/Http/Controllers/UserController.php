<?php

namespace App\Http\Controllers;

use App\UseCases\User\show;
use App\UseCases\User\Index;
use App\UseCases\User\Login;
use App\UseCases\User\Create;
use App\UseCases\User\Update;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\UseCases\User\CreateFirstUser;
use App\Http\Responses\DefaultResponse;
use App\Http\Requests\User\IndexRequest;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\User\ShowResource;
use App\Http\Resources\User\LoginResource;
use App\UseCases\Params\User\CreateParams;
use App\UseCases\Params\User\UpdateParams;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Resources\User\CreateResource;
use App\Http\Resources\User\UpdateResource;
use App\Http\Resources\User\RegisterResource;
use App\UseCases\Params\User\CreateFirstUserParams;
use App\Http\Resources\User\IndexCollectionResource;

class UserController extends Controller
{
    /**
     * Para primeiro acesso, cria um usuário MANAGER e a empresa
     *
     * POST api/users/register
     *
     * @param RegisterRequest $request
     *
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        //TODO: sugestão CreateFirstUserParams poderia ter uma função chamada fromArray CreateFirstUserParams::fromArray($request->validated());
        $params = new CreateFirstUserParams(
            $request->company_name,
            $request->company_document_number,
            $request->user_name,
            $request->user_document_number,
            $request->email,
            $request->password
        );

        $useCase  = new CreateFirstUser($params);
        $response = $useCase->handle();

        return $this->response(
            new DefaultResponse(
                new RegisterResource(
                    $response
                )
            )
        );
    }

    /**
     * Endpoint de login (autenticação BASIC)
     *
     * POST api/users/login
     *
     * @return JsonResponse
     */
    public function login(): JsonResponse
    {
        //TODO: entendo o uso da autenticação basic por ser um teste, mas ela gera problemas de segurança em uma api que lida com informações sensíveis 
        //TODO: talvez remova isso
        $response = (new Login(Auth::id()))->handle();

        return $this->response(
            new DefaultResponse(
                new LoginResource(
                    $response
                )
            )
        );
    }

    /**
     * Endpoint de listagem de usuários
     *
     * GET api/users
     *
     * @return JsonResponse
     */
    public function index(IndexRequest $request): JsonResponse
    {
        //TODO: o nome do dominio Index me parece estranho para um caso de uso, talvez ListUsers ou ListUser seja mais apropriado
        $response = (new Index( 
            Auth::user()->company_id,
            $request->name,
            $request->email,
            $request->status
        ))->handle();

        return $this->response(
            new DefaultResponse(
                new IndexCollectionResource($response) //TODO: UserCollectionResource me parece mais apropriado
            )
        );
    }

    /**
     * Endpoint de dados de usuário
     *
     * GET api/users/{id}
     *
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $response = (new show($id, Auth::user()->company_id))->handle();

        return $this->response(
            new DefaultResponse(
                new ShowResource($response)
            )
        );
    }

    /**
     * Endpoint de criação de usuário
     *
     * POST api/users
     *
     * @return JsonResponse
     */
    public function create(CreateRequest $request): JsonResponse
    {
        //TODO: sugestão criar função fromArray
        $params = new CreateParams(
            Auth::user()->company_id,
            $request->name,
            $request->document_number,
            $request->email,
            $request->password,
            $request->type
        );

        $response = (new Create($params))->handle();

        return $this->response(
            new DefaultResponse(
                new CreateResource($response)
            )
        );
    }

    /**
     * Endpoint de modificação de usuário
     *
     * PATCH api/users/{id}
     *
     * @return JsonResponse
     */
    public function update(string $id, UpdateRequest $request): JsonResponse
    {
        //TODO: sugestão criar função fromArray
        $params = new UpdateParams(
            $id,
            Auth::user()->company_id,
            $request->name,
            $request->email,
            $request->password,
            $request->type
        );

        $response = (new Update($params))->handle();

        return $this->response(
            new DefaultResponse(
                new UpdateResource($response)
            )
        );
    }
}

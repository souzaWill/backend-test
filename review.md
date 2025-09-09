

## 1. Injeção de dependência ausente / Instanciação direta
No projeto falta o uso de injeção de dependência, está sendo instanciado as classes concretas ao inves de uma interface, com isso quebrando o Princípio da Inversão de Dependência (DIP) 
```php
//app/UseCases/Card/Register.php
/**
     * Registra no banco de dados
     *
     * @return void
     */
    protected function store(RegisterDomain $domain): void
    {
        (new Create($domain))->handle();
    }

```
### Sugestão de solução:
1. ao inves de instanciar a classe concreta da repository, utilizar injeção de dependencia, preferencialmente utilizando interfaces 
```php
<?php
namespace App\UseCases\Card;

use App\Repositories\Card\CreateCardRepositoryInterface;

class Register extends BaseUseCase
{
    public function __construct(string $userId, string $pin, string $cardId, CreateCardRepositoryInterface $createCardRepositoryInterface)
    {
        $this->userId = $userId;
        $this->pin    = $pin;
        $this->cardId = $cardId;
        $this->createCardRepositoryInterface = $createCardRepositoryInterface;
    }

    * Registra no banco de dados
     *
     * @return void
     */
    protected function store(Card $card): void
    {
        $createCardRepositoryInterface->handle($domain);
    }
}



```
essa interface deve servir como um contrato, e não deve expor como é implementada.

2. Implemente a interface na repository:
```php
class CreateCardRepository implements CreateCardRepositoryInterface
{
    public function handle(string $userId)
    {
        // ...
    }
}

```

4. Registrar o bind no AppServiceProvider
```php
//app/Providers/AppServiceProvider.php

public function register()
{
    $this->app->bind(CreateCardRepositoryInterface::class, CreateCardRepository::class);
}
```
O bind no AppServiceProvider garante que, quando a aplicação solicitar CreateCardRepositoryInterface, o Laravel saiba qual implementação concreta (CreateCardRepository) deve ser entregue automaticamente.

### Observação:
Criar uma interface por método (CreateCardRepositoryInterface -> CreateCardRepository) não seria o ideal, normalmente criamos uma interface para o repositório inteiro (ex: CardRepositoryInterface)
```php
namespace App\Repositories\Account;

interface CardRepositoryInterface
{
    public function findByUser(string $userId);
    public function save(Card $data);
    // outros contratos
}
```
porem irei falar melhor disso quando discutir sobre a implementação das repositories.

### Ocorrencias:
- app/Domains/Card/Register.php (linhas 54, 73)
- app/Domains/User/Create.php (linha 81)
- app/UseCases/Account/Register.php (linha 55)

## 2. Observações sobre as Repositories
<pre>
├── Account
│   ├── Create.php
│   ├── FindByUser.php
│   └── UpdateStatus.php
├── BaseRepository.php
├── Card
│   ├── CanUseExternalId.php
│   ├── Create.php
│   └── FindByUser.php
├── Company
│   ├── CanUseDocumentNumber.php
│   ├── Create.php
│   ├── Find.php
│   └── Update.php
├── Interfaces
│   └── BaseRepositoryInterface.php
├── Token
│   └── Create.php
└── User
    ├── CanUseDocumentNumber.php
    ├── CanUseEmail.php
    ├── Create.php
    ├── Find.php
    ├── Retrieve.php
    └── Update.php

</pre>
nesse projeto foi implementado uma ```BaseRepository.php```que abstrai a as funções da camada de persistencia:

```php
namespace App\Repositories;

abstract class BaseRepository implements BaseRepositoryInterface
{
	public function create(array $attributes): array
    {
        return $this->instance::create($attributes)
		->withoutRelations()
		->toArray();
    }
}
```
```php
namespace App\Repositories\Interfaces;

interface BaseRepositoryInterface
{
	public function create(array $attributes): array;
}
```

ai existe uma classe para cada ação que pode ser feita dentro da repository, algo que se assemelha ao Command Pattern, Command Bus / Handler pattern.

```php
namespace App\Repositories\User;

class Create extends BaseRepository
{

    /**
     * Criação de usuário
     *
     * @return array
     */
    public function handle(): array
    {
        return $this->create(
            [
                'company_id'      => $this->domain->companyId,
                'name'            => $this->domain->name,
                'document_number' => $this->domain->documentNumber,
                'email'           => $this->domain->email,
                'password'        => $this->domain->password,
                'type'            => $this->domain->type,
            ]
        );
    }

}
```
### Pontos positivos
- Separação de responsabilidades.
- Flexibilidade para extensão. 

### Pontos negativos
- Verboso / muito boilerplate
- Acoplamento indireto

Apesar do BaseRepository abstrair, cada comando ainda depende da implementação concreta do ORM ($this->instance::create), o que dificulta trocar de ORM ou usar outra camada de persistência.
alem disso em alguns lugares e usado a proprio ``$this->builder`` do eloquent que foi definido na ``BaseRepository``
```php
namespace App\Repositories\User;

class CanUseDocumentNumber extends BaseRepository
{
	/**
     * Valida se o documento é único
     *
     * @return bool
     */
    public function handle(): bool
    {
        $user = $this->builder
            ->where('document_number', $this->document_number) 
            ->first();
			//where e first e uma função do proprio eloquent, porque nao abstrair no BaseRepository


        return is_null($user);
    }
}
```
## Sugestão de melhoria
1. Como a implementação de repositories atualmente e acoplada diretamente ao Eloquent, moveria as implementações atuais para uma pasta ```app/Repositories/Eloquent/User/CanUseDocumentNumber.php```
2. na raiz das repositories ```app/Repositories/``` criaria interfaces que serviriam de contrato para os useCases acessarem ex: ```app/Repositories/User/CanUseDocumentNumberInterface.php```
3. faria o bind entre a interface e a classe concreta.
3. fosse necessario alguma chamada a repository, realizaria injeção de dependencia da mesma, usando a sua interface ao inves da classe.


## 3. Organização e Responsabilidade das Camadas
irei citar dois exemplos que se repetem durante varios momentos no codigo

### app/Http/Controllers/CompanyController.php

```php
    //app/Http/Controllers/CompanyController.php
	
    /**
     * Endpoint de modificação de empresa
     *
     * PATCH api/company
     *
     * @return JsonResponse
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $dominio = (new UpdateDomain(
            Auth::user()->company_id,
            $request->name,
        ))->handle();

        (new CompanyUpdate($dominio))->handle();

        $resposta = Company::find(Auth::user()->company_id)->first()->toArray();

        return $this->response(
            new DefaultResponse(
                new UpdateResource($resposta)
            )
        );
    }
```

#### Problemas identificados 
- a controller nao deveria estar chamando o dominio diretamente, deveria estar passando pelo ``app/UseCases/Company/update`` que orquestraria tanto o ``App\Domains\Company\Update`` quanto a repository ``App\Repositories\Company\Update``
- as variaveis com o nome em portugues
- a chamada a model nao devia estar ali, o useCase deveria retornar os dados da company

### app/Domains/Card/Register.php
```php
use App\Repositories\Card\CanUseExternalId;

class Register extends BaseDomain
{
    protected function checkExternalId(): void
    {
        if (!(new CanUseExternalId($this->cardId))->handle()) {
            throw new InternalErrorException(
                'Não é possível vincular esse cartão',
                0
            );
        }
    }
}
```
#### Problemas identificados 

- As chamadas aos repositórios devem ser realizadas pelos UseCases correspondentes, e não pelo Domain, para evitar acoplamento dessa camada com a infraestrutura ou APIs.
- tambem deveria ser usado injeção de dependências FindByUser e CanUseExternalId no método construtor ou no handle

### app/Domains/Card/Register.php

```php
//app/Domains/User/Create.php
class Create extends BaseDomain
{
    /**
     * Encripta a senha
     *
     * @param string $password
     *
     * @return void
     */
    protected function cryptPassword(string $password): void
    {
        $this->password = Hash::make($password);
    }
}
```

#### Problemas identificados 
- o dominio ```User/Create``` tem acesso direto a implementação de infraestrutura ```Hash::make()``, isso gera um problema de acoplamento
- o ideal seria usar a injeção de dependência com uma interface (ex: ```PasswordHasherInterface```) e em uma camada de infra criar uma classe concreta que realiza a criptografia do password


## 4. integridade de dados
- app/UseCases/Account/Block.php
```php
    public function handle(): void
    {
        try {
            $this->updateDatabase();
            $this->updateStatus();
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'userId' => $this->userId,
                ]
            );
        }
    }
``` 
- para garantir a integridade, primeiro atualizar no externo e depois no banco, caso falhe no externo nao atualiza no banco, caso falhe no banco ja atualizou no externo
- talvez usar transacoes, para isso uma ``TransactionInterface`` que implemente ``begin``, ``commit``, ``rollback`` sem expor o banco de dados e uma ``infra/Database/TransactionService`` que implemente essas funcoes.

## 5. Falta de exceptions específicas
esse caso depende do que pode ser exposto para o client, no projeto foi criado a app/Exceptions/InternalErrorException.php, e ela e usada em algumas validações

```php
//app/Domains/Card/Register.php

if (is_null($account)) {
	throw new InternalErrorException(
		'ACCOUNT_NOT_FOUND',
		161001001
	);
}
```
criaria um AccountNotFoundException e uma classe ErrorCodes para evitar o uso de numeros magicos direto no codigo:
```php
//app/Domains/Card/Register.php

if (is_null($account)) {
	throw new AccountNotFoundException();
}
```

```php
// app/Exceptions/Codes/ErrorCodes.php

namespace App\Exceptions\Codes;

class ErrorCodes
{
    public const ACCOUNT_NOT_FOUND = 161001001;

    // aqui você pode adicionar outros
    // public const USER_NOT_FOUND = 161001002;
    // public const INVALID_CREDENTIALS = 161001003;
}
```
```php

// app/Exceptions/AccountNotFoundException.php

use App\Exceptions\Codes\ErrorCodes;

class AccountNotFoundException extends BaseException
{
    public function __construct(
        string $message = 'ACCOUNT_NOT_FOUND',
        int $code = ErrorCodes::ACCOUNT_NOT_FOUND
    ) {
        parent::__construct($message, $code);
    }
}

```
### Observação:
o ideal seria o uso de enums porem o projeto usa php 8.1, funcionariam melhor para pegar a $message tambem 
### Ocorrencias:

- app/Domains/Card/Register.php (linhas 58, 75)
- app/Domains/Company/Create.php (linhas 37)
- app/Domains/User/Create.php (linhas 93, 110, 127)
- app/UseCases/Account/Register.php (linha 58)

## 6. Exposição de dados sensíveis em logs
- app/Traits/Logger.php (linha 167)
- app/Exceptions/Handler.php (linha 136)

```php
dump($exception);

// Resultado aproximado do dump
App\Exceptions\AccountNotFoundException {#123
  #message: "ACCOUNT_NOT_FOUND"
  #code: 161001001
  #file: "/path/to/project/app/Repositories/Account/FindByUser.php"
  #line: 42
  #trace: array:3 [...]
}

```
1. o ``dump($exception)`` está expondo o stack trace completo, oque pode ser util desenvolvimento, porem perigoso em produção, uma abordagem seria
usar a env ``APP_DEBUG`` do laravel.

app/Traits/Logger.php
```php
if(config('app.debug', false)){
	dump($exception);
}
```
- app/UseCases/Card/Register.php (linha 92)

```php
catch (Throwable $th) {
	$this->defaultErrorHandling(
		$th,
		[
			'userId' => $this->userId,
			'pin'    => $this->pin, //pin pode ser um dado sensivel
			'cardId' => $this->cardId,
		]
	);
}
```
nesse caso recomendaria a remoção.


## 7. Acoplamento excessivo ao Eloquent / uso inadequado de builder

```php
// app/Repositories/User/Retrieve.php


class Retrieve extends BaseRepository
{
	/**
     * Lista de usuários (Paginado)
     *
     * @return LengthAwarePaginator
     */
    public function handle(): LengthAwarePaginator
    {
        //TODO: evitar o whereRaw, o eloquent tem suporte a like nativamente, porem seria melhor abstrair isso na BaseRepository
        $this->leftJoinAccount();

        //TODO: extrair esses filtros para uma função privada para melhorar a legibilidade
        if ($this->name) {
            $this->builder->whereRaw("name LIKE '%" . $this->name . "%'");
        }

        if ($this->email) {
            $this->builder->whereRaw("email LIKE '%" . $this->email . "%'");
        }

        if ($this->status) {
            //TODO: INACTIVE deveria ser uma constante ou um enum
            if ($this->status === 'INACTIVE') {
                $this->builder->whereRaw('accounts.id IS NULL');
            } else {
                $this->builder->whereRaw('accounts.status = "' . $this->status . '"');
            }
        }

        //TODO: usar o $this->builder o acoplamento entre a repository o eloquento fica maior ainda
        // assim dificulta a troca de implementação, testabilidade, etc... 
        // como solução recomendaria uma abstração dessa funcão na BaseRepository
        $this->builder->where('company_id', $this->companyId)
            ->orderBy('name');

        return $this->paginate(['users.*']);
    }
}
```

### Problemas identificados 
- primeiramente, nao se faz necessaria a consulta com ```whereRaw()``` nesse codigo sendo que o eloquent tem suporte ao like nativamente.
- usar o ```$this->builder``` o acoplamento entre a repository o eloquent, a repository nao deveria ter acesso a implementação concreta do eloquent, e muito menos fazer uma chamada direta a uma função do mesmo como ```whereRaw``` ```orderBy```etc...
- nesse caso o ideal, seguindo a arquitetura proposta seria o a abstração dessas funcionalidades na BaseRepository, porem isso deixaria a ```BaseRepository``` ainda maior

### Ocorrencias 
- app/Repositories/BaseRepository.php (linha 13)
- app/Repositories/Account/FindByUser.php (linha 41`
- app/Repositories/Card/FindByUser.php (linha 57, 61)
- app/Repositories/Token/Create.php (linha 48)
- app/Repositories/User/Find.php (linha 49)
- app/Repositories/User/Retrieve.php (linha 102)
- app/Repositories/User/Update.php (linha 42)

## 8. Consultas completas ao invés de exists
em alguns trechos onde e feita uma consulta no banco de dados para verificar a existencia de um item, esta sendo utilizado o ``first()`` quando poderia ser usado o `exists()`

```php
//app/Repositories/Card/CanUseExternalId.php

public function handle(): bool
{
	$user = $this->builder
		->where('external_id', $this->externalId)
		->first(); 

	return is_null($user);
}
```
como o retorno da função e um bool, o uso do `exists()`` simplificaria o codigo alem do ganho de perfomance pois nao seria necessario trazer o registro inteiro o sql ficaria algo parecido com isso:
```sql
SELECT EXISTS(
    SELECT 1 FROM cards WHERE `external_id` = 'valor'
) as `exists`;
```
e o codigo assim:
```php
public function handle(): bool
{
	return $this->builder
		->where('external_id', $this->externalId)
		->exists(); 
}
```

### Observação:

a essas repositories que utilizam o ``$this->builder`` expoe um problema sobre a implementação da camada de repository, esse builder acaba acoplando a repository diretamente ao eloquent, sendo que isso nao deveria acontecer, como solução paliativa sem uma real ajustada na estrutura da camada de repositorio sujiro a abstração dessas funções na BaseRepository algo como ``existsBy(collum,value)`` BaseRepository

### Ocorrencias:

- app/Repositories/Card/CanUseExternalId.php (linha 43)
- app/Repositories/Company/CanUseDocumentNumber.php (linha 43)
- app/Repositories/User/CanUseDocumentNumber.php (linha 44)
- app/Repositories/User/CanUseEmail.php (linha 44)

## 9. Falta de uso de Enum/Constantes para valores fixos

```php
// app/Http/Requests/User/UpdateRequest.php
    public function rules(): array
    {
        return [
            'name'     => 'sometimes|nullable',
            'email'    => 'sometimes|nullable|email',
            'password' => 'sometimes|nullable',
            'type'     => 'sometimes|nullable|in:USER,VIRTUAL,MANAGER'
        ];
    }
```

```php
    protected function checkType(): void
    {
        if (!in_array($this->type, ['USER', 'VIRTUAL', 'MANAGER'])) {
            throw new InternalErrorException(
                'Não é possível adicionar o tipo informado',
                0
            );
        }
    }
```

```php
//app/Policies/BasePolicy.php

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
```

se repete no codigo em strings o tipo de usuario que pode ser ``USER,VIRTUAL,MANAGER``, definir uma constante para esses 3 tipos traria uma série de benefícios claros para o projeto como melhor legibilidade, evitaria a duplicidade, facilitaria futuras adições e etc...
devido o projeto ainda nao ter suporte ao uso de enums, usaria uma classe de constantes:

```php
namespace App\Domains\User;

class UserType
{
    public const USER    = 'USER';
    public const VIRTUAL = 'VIRTUAL';
    public const MANAGER = 'MANAGER';

    /**
     * Retorna todos os tipos válidos
     */
    public static function all(): array
    {
        return [
            self::USER,
            self::VIRTUAL,
            self::MANAGER,
        ];
    }
}

```


e o uso ficaria assim:

```php
// app/Http/Requests/User/UpdateRequest.php
public function rules(): array
{
	return [
		'name'     => 'sometimes|nullable',
		'email'    => 'sometimes|nullable|email',
		'password' => 'sometimes|nullable',
		'type'     => 'sometimes|nullable|in:' . implode(',', UserType::all()),
	];
}
```
```php
// app/Policies/BasePolicy.php

protected function isManagerAccountsUser(): void
{
    $user = Auth::user();
    if (!is_null($user) && $user->type !== UserType::MANAGER) {
        $this->deny(
            'UNAUTHORIZED',
            146001003,
            Auth::id(),
            UserType::User
        );
    }
}}

```

- app/Http/Requests/User/CreateRequest.php (linha 21)
- app/Http/Requests/User/UpdateRequest.php (linha 20)
- app/Repositories/User/Retrieve.php (linha 94`

## 10. Nomenclatura fora do padrão
achei alguns casos de nomeclaturas que quebram os padrões estabelecidos

```php
//app/UseCases/User/Login.php

use App\Repositories\Token\Create as create_token; 
//devia ser CreateToken porem tambem nao existe a necessidade do alias

protected function createToken(): void
{
	$this->token = (new create_token($this->id))->handle();
}
```
```php
// app/UseCases/User/show.php

class show extends BaseUseCase
// devia ser Show
{
    public function __construct(string $a, string $b)
    {
        // nomes fora do padrão, usar nomes mais descritivos
        $this->a = $a //id;
        $this->b = $b //companyId;
    }
}
```

```php
// app/Domains/Company/Create.php

class Create extends BaseDomain 
// classe nao condiz com o nome, poderia ser CompanyValidator
{
    public function handle(): self
    {
        $this->checkDocumentNumber();

        return $this;
    }
}
```

```php
// app/Http/Controllers/UserController.php

/**
* Endpoint de listagem de usuários
*
* GET api/users
*
* @return JsonResponse
*/
public function index(IndexRequest $request): JsonResponse
{
	//o nome do dominio Index me parece estranho para um caso de uso, talvez ListUsers ou ListUser seja mais apropriado
	$response = (new Index( 
		Auth::user()->company_id,
		$request->name,
		$request->email,
		$request->status
	))->handle();

	return $this->response(
		new DefaultResponse(
			new IndexCollectionResource($response)
		)
	);
}
```

### Alem disso classes com nomes genéricos como ```Create```, ```Update```, ```Show```, etc. não ajudam a entender o contrato da classe preferir nomes descritivos

### Ocorrencias
- app/UseCases/User/Login.php (linha 7)
- app/UseCases/User/show.php (linha 9, 35)
- app/Domains/Company/Create.php (linha 9)
- app/Http/Controllers/UserController.php (linha 95, 105)

## 11. Reinventando recursos nativos do Laravel
app/Http/Middleware/App/ResourcePolicies.php
```php
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
        
        app(self::POLICIES_NAMESPACE . '\\' . $resource)->{$method}(...$parameters);
    }
```

acredito que teria o mesmo efeito se utilizar o ->middleware('can:view,user'); em cada rota ou $this->authorizeResource(User::class, 'user'); em cada controller

```php
// app/Http/Controllers/UserController.php
class UserController extends Controller
{
    public function __construct() {
        $this->authorizeResource(User::class, 'user');
}

// routes/api.php
Route::get('', [UserController::class, 'index'])->can('viewAny', 'App\Models\User');

```
usar funcionalidades nativas do laravel ajudam a nao precisar reinventar a roda, pois elas foram validadas pela comunidade, e diminuem pontos de futuros erros.
o mesmo ocorre com a  ``app/Traits/Instancer.php``
```php

trait Instancer
{
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
```
a não ser que seja um edge case muito especifico, não entendo o beneficio dessa trait contra o uso do ``app()->make()`` ou ``resolve()``, ou ate mesmo o ``new``do php.

### Ocorrencias
- app/Http/Middleware/App/ResourcePolicies.php (linha 8, 9)
- app/Traits/Instancer.php (linha 7)

## 12. porque não usar Gates
a classe app/Policies/BasePolicy.php mistura a verificação de autorização com logging, talvez fosse melhor separar essas responsabilidades.
uma sugestão seria o uso da Facade Gate::
assim ao inves de definir a função assim:
```php
    /**
     * Verifica se o usuário dado é do usuário logado, ou se ele
     * é gestor
     *
     * @return void
     */
    protected function isManagerOrOwnerResource(string $ownerResourceId): void
    {
        $user = Auth::user();

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
```
podemos colocar a logica para definir se e um manager ou owner na camada de Gate:
```php
// app/Providers/AuthServiceProvider.php

    Gate::define('manager-or-owner', function (User $user, string $ownerId) {
        return $user->id === $ownerId || $user->type === 'MANAGER';
    });
```
assim na tiraria essa logica da função isManagerOrOwnerResource(), e la so seria usado para validar
```php
protected function isManagerOrOwnerResource(string $ownerResourceId): void
{
	if (Gate::denies('manager-or-owner', $ownerResourceId)) {
		$this->deny('UNAUTHORIZED', 146001003, Auth::id(),'USER');
	}
}
```
## 13. Funções/classes não utilizadas ou sem efeito
no codigo a diversas funções que nao estão sendo utilizadas ou sem efeito
```php
// app/Domains/Company/Update.php
class Update extends BaseDomain
{
	/**
     * Checa se é possível modificar a empresa
     *
     * @return self
     */
    public function handle(): self
    {
        // Nenhuma validação necessária

        return $this;
    }
}
```
- app/Domains/Company/Update.php (linha 37)
- app/Repositories/Card/FindByUser.php (linha 34)
- app/Http/Requests/Traits/SanitizesInput.php (linha 7)

## 14. Comentários que induzem a erro ou estão desatualizados
diversos comentarios estão induzindo ao erro


```php
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
```
essa api e um GET porem na doc está POST

```php
    /**
     * Busca os dados de conta
     *
     * @return void
     */
    protected function findAccountData(): void
    {
        $account = (new FindByUser($this->userId))->handle();

        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001 //TODO: criar constante para esse codigo de erro ou erro persoalizado
            );
        }

        $this->externalId = $account['external_id'];
    }
```
essa função esta mais para ``setExternalId()``

## 15. Uso de cache
isso daqui não se caracteriza como um problema, porem e uma implementação legal do laravel

- app/Integrations/Banking/Gateway.php (linha 97, 136)
```php
    /**
     * Função responsável por gerar um novo token ou obter um toke válido já gerado.
     *
     * @return void
     */
    public function generateAuthenticationToken(): void
    {
        $token = Cache::get('banking_authentication_token');

        if ($token !== null) {
            $this->setAuthenticationToken($token);

            return;
        }

        $data = $this->newClient()
            ->post(
                $this->getAuthUrl(),
                [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->getClientId(),
                    'client_secret' => config('auth.banking_client_secret'),
                ]
            )
            ->json();

        $token = $data['access_token'] ?? null;

        if ($token !== null) {
            Cache::put('banking_authentication_token', $token, 180);
            $this->setAuthenticationToken($token);
        }
    }
```
daria para ser usado dessa maneira usando Cache::remember
```php
    public function generateAuthenticationToken(): void
    {
        $token = Cache::remember('banking_authentication_token', 180, function () {
            return $this->newClient()
                ->post(
                    $this->getAuthUrl(),
                    [
                        'grant_type'    => 'client_credentials',
                        'client_id'     => $this->getClientId(),
                        'client_secret' => config('auth.banking_client_secret'),
                    ]
                )
                ->json()['access_token'] ?? null;
        });
        $this->setAuthenticationToken($token);
    }
```

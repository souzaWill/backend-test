
## app/Domains/Card/Register.php
- 52: mudar para metodo privado
- 54: usar injeção de dependência ao invés de instanciar diretamente
- 58: Criar uma exception específica para conta não encontrada
- 71: mudar para metodo privado
- 73: usar injeção de dependência ao invés de instanciar diretamente
- 75: Criar uma exception específica para cartão já vinculado
- 92: remover pin dos logs, é um dado sensível

## app/Domains/Company/Create.php
- 9: classe nao condiz com o nome, poderia ser CompanyValidator
- 34: mudar para metodo privado
- 37: Criar uma exception específica para documento já vinculado

## app/Domains/Company/Update.php
- 37: classe sem efeito, remover ou implementar lógica

## app/Domains/User/Create.php
- 81: remover a função de hash direta, usar injeção de dependência ex: bind PasswordHasherInterface -> LaravelPasswordHasher
- 93: Criar uma exception especifica para email já vinculado
- 108: [TODO sem descrição]
- 110: Criar uma exception específica para documento já vinculado
- 127: Criar uma exception específica para tipo inválido

## app/Domains/User/Update.php
- 80: remover a função de hash direta, usar injeção de dependência ex: bind PasswordHasherInterface -> LaravelPasswordHasher
- 112: código duplicado, existe a mesma validação em User/Create.php, considerar o uso de ValueObjects ou um Enum

## app/Exceptions/Handler.php
- 136: remover dump
- 190: talvez mover o pattern para o arquivo de configuração, ex: config/patterns.php

## app/Http/Controllers/AccountController.php
- 17: esses docs podem induzir a erro, pois essa função não ativa a conta, apenas registra a conta no sistema, com o status BLOCK, após isso é necessário chamar a rota de active para ativar a conta

## app/Http/Controllers/CardController.php
- 14: no phpdoc, está descrevendo que o método é Post, quando na realidade é GET
- 24: chamada direta a camada de integração, foge do padrão de useCases
- 32: aparentemente este método registra um cartão, mas o phpdoc diz que ativa um cartão
- 42: usar validation form request para validar os dados de entrada, por exemplo se o pin e card_id foram enviados pois são como string, caso contrário da erro
- 44: criar um UseCases/Params/Card/RegisterParams para evitar passar vários parâmetros soltos

## app/Http/Controllers/CompanyController.php
- 46: variáveis fugindo do padrão em inglês utilizado no projeto
- 47: chamando o domínio direto na controller, fugindo do padrão de usar o usecase
- 53: chamando o repositório direto na controller, fugindo do padrão, nesse caso aqui deveria ser

## app/Http/Controllers/HealthCheckController.php
- 11: comentário induz a erro pois o endpoint é GET e não POST

## app/Http/Controllers/UserController.php
- 42: sugestão CreateFirstUserParams poderia ter uma função chamada fromArray CreateFirstUserParams::fromArray($request->validated());
- 73: entendo o uso da autenticação basic por ser um teste, mas ela gera problemas de segurança em uma api que lida com informações sensíveis
- 74: talvez remova isso
- 95: o nome do domínio Index me parece estranho para um caso de uso, talvez ListUsers ou ListUser seja mais apropriado
- 105: UserCollectionResource me parece mais apropriado
- 137: sugestão criar função fromArray
- 165: sugestão criar função fromArray

## app/Http/Middleware/App/ResourcePolicies.php
- 8: acho que o laravel já tem algo nativo que faz isso, reinventar a roda às vezes não é a melhor ideia, mas depende de entender as reais necessidades
- 9: ->middleware('can:view,user'); ou a cada controller usar $this->authorizeResource(User::class, 'user'); no construtor
- 56: erros não tratados caso a classe ou o método não existam, hoje seu código vai lançar um Error genérico (Call to undefined method).
- 57: conferir se deixo esse comentário

## app/Http/Requests/Traits/SanitizesInput.php
- 7: essa trait parece não ser usada, ou estou errado?
- 8: estranho ter uma pasta Traits dentro de Requests, talvez fosse melhor em App/Traits
- 9: verificar se eu estou certo kkkkk

## app/Http/Requests/User/CreateRequest.php
- 21: poderia ser um enum, pois esses tipos estão espalhados pelo sistema

## app/Http/Requests/User/UpdateRequest.php
- 20: poderia ser um enum, pois esses tipos estão espalhados pelo sistema

## app/Integrations/Banking/Account/Find.php
- 42: criar constante para esse código de erro ou erro personalizado
- 46: o nome dessa função está errado, pois o que ela faz de verdade é setar o externalId
- 71: o mais adequado para o nome da variável seria $response

## app/Integrations/Banking/Gateway.php
- 97: createLog é chamado 3x, com parâmetros quase iguais, talvez o ideal seria criar um default e ir sobrescrevendo o que for diferente
- 136: usaria o Cache::remember('key', ttl, callback), pois com ele é possível buscar o item na cache, caso não exista ele busca o item e já seta na cache

## app/Integrations/Banking/Params/Account/CreateParams.php
- 62: função não utilizada, talvez remover a classe

## app/Models/Account.php
- 33: nesse caso não é necessário especificar as chaves estrangeiras, pois seguem o padrão do Laravel

## app/Policies/BasePolicy.php
- 9: essa classe mistura a verificação de autorização com logging, talvez fosse melhor separar essas responsabilidades
- 104: ifs anihilados deixam a lógica mais complexa do que ela realmente é, um early return deixaria mais claro

## app/Repositories/Account/FindByUser.php
- 41: usar o $this->builder o acoplamento entre a repository o eloquento fica maior ainda

## app/Repositories/BaseRepository.php
- 13: a implementação de repository do projeto está muito acoplada ao Eloquent

## app/Repositories/Card/CanUseExternalId.php
- 43: usar exists, que é mais performático pois não precisa trazer o registro completo
- 44: porém o ideal seria a abstração disso na BaseRepository

## app/Repositories/Card/FindByUser.php
- 34: essa função não está sendo chamada em lugar nenhum
- 57: usar o $this->builder o acoplamento entre a repository o eloquento fica maior ainda
- 61: faltou chamar o joinAccount aqui

## app/Repositories/Company/CanUseDocumentNumber.php
- 43: usar exists, que é mais performático pois não precisa trazer o registro completo
- 44: porém o ideal seria a abstração disso na BaseRepository

## app/Repositories/Token/Create.php
- 7: criar interface TokenRepositoryInterface e implementar aqui, assim facilitando testes unitários e possível troca de implementação
- 9: avaliar comentário
- 37: aqui fugiu do padrão, de definir a model em um método setModel()
- 48: aqui está fugindo do padrão de usar $this->builder, assim o acoplamento entre a repository e o eloquent fica maior ainda

## app/Repositories/User/CanUseDocumentNumber.php
- 44: usar exists, que é mais performático pois não precisa trazer o registro completo
- 45: porém o ideal seria a abstração disso na BaseRepository

## app/Repositories/User/CanUseEmail.php
- 44: usar exists, que é mais performático pois não precisa trazer o registro completo
- 45: porém o ideal seria a abstração disso na BaseRepository

## app/Repositories/User/Find.php
- 49: usar o $this->builder o acoplamento entre a repository o eloquento fica maior ainda

## app/Repositories/User/Retrieve.php
- 81: evitar o whereRaw, o eloquent tem suporte a like nativamente, porém seria melhor abstrair isso na BaseRepository
- 84: extrair esses filtros para uma função privada para melhorar a legibilidade
- 94: INACTIVE deveria ser uma constante ou um enum
- 102: usar o $this->builder o acoplamento entre a repository o eloquento fica maior ainda

## app/Repositories/User/Update.php
- 42: usar o $this->builder o acoplamento entre a repository o eloquento fica maior ainda

## app/Traits/Instancer.php
- 7: o laravel nativamente já faz isso com o app()->make() ou resolve()

## app/Traits/Logger.php
- 106: canal log_service não existe
- 167: não expor o stack tracing da exceção em produção, interessante verificar APP_DEBUG e true, caso sim exibir

## app/UseCases/Account/Active.php
- 38: talvez seria criar um domínio para account, assim, o domain orquestraria a repository e a integração, seria o jeito mais DDD
- 59: para garantir a integridade, primeiro atualizar no externo e depois no banco, caso falhe no externo não atualiza no banco, caso falhe no banco já atualizou no externo

## app/UseCases/Account/Block.php
- 38: talvez seria criar um domínio para account, assim, o domain orquestraria a repository e a integração, seria o jeito mais DDD
- 59: para garantir a integridade, primeiro atualizar no externo e depois no banco, caso falhe no externo não atualiza no banco, caso falhe no banco já atualizou no externo

## app/UseCases/Account/Register.php
- 55: usar injeção de dependência ao invés de instanciar diretamente
- 58: Criar uma exception específica para usuário não encontrado
- 98: usar transações para garantir a integridade, caso falhe em algum ponto, desfazer o que já foi feito

## app/UseCases/Card/Register.php
- 92: remover pin dos logs, é um dado sensível

## app/UseCases/User/Login.php
- 7: nomear create_token para CreateToken pois não segue o padrão PascalCase

## app/UseCases/User/show.php
- 9: nomear show para Show pois não segue o padrão PascalCase
- 35: nomes fora do padrão, usar nomes mais descritivos


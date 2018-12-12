<?php

namespace App\Http\Requests;

use App\Http\Responses\ResponseException;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 *
 * Implementação de RequestHandlerInterface responsavel por executar a fila de middlewares
 * As filas são definidas em arrays de mapeamentos
 *
 * @author William Costa
 *
 */
class QueueMapRequestHandler implements RequestHandlerInterface {

    /**
     * Mapa de execuções
     * O mapa deve ser composto de uma ou mais implementações de MiddlewareInterface
     * e de uma implementação de RequestHandlerInterface.
     * A implementação de RequestHandlerInterface deve ser o último índice do array de mapa
     *
     * Exemplo:
     *          [
     *             "App\\Http\\Middlewares\\NomeDoMiddleware" => [parametros do construtor]
     *             "App\\Http\\Middlewares\\NomeDoMiddleware2" => [parametros do construtor]
     *             ...
     *             "App\\Http\\Request\\SuccessRequestHandler" => [parametros do construtor]
     *          ]
     *          
     * @var array
     */
    private $map = [];

    /**
     * Responsável por definir o mapa de execuções
     * @method __construct
     * @param  array        $map
     */
    public function __construct($map){
        $this->map = $map;
    }

    /**
     * Método responsável por obter o mapa de execuções de um arquivo de mapas
     * @method getMap
     * @param  string $mapName
     * @return array
     */
    public static function getMap($mapName){
      $map = __DIR__.'/../../Maps/'.$mapName.'.json';
      return file_exists($map) ? json_decode(file_get_contents($map),true) : null;
    }

    /**
     * Método responsável por obter a instancia atual do map
     * @method getInstanceMap
     * @return mixed
     */
    public function getInstanceMap(){
      $atual = array_shift($this->map);
      if(!is_array($atual)){
        throw new \Exception("Nenhuma instancia de manipulador de requisição mapeada", 400);
      }

      $interface = empty($this->map) ? 'Psr\Http\Server\RequestHandlerInterface' : 'Psr\Http\Server\MiddlewareInterface';

      $keys  = array_keys($atual);
      $class = reset($keys);
      $instancia = new $class($atual[$class]);

      if(!is_a($instancia,$interface)){
        throw new \Exception("A classe ".$class." não implementa a interface ".$interface, 400);
      }

      return $instancia;

    }

    /**
     * Método responsável por processar a requisição e retornar uma response
     * @method handle
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
      try{

        //EXECUTA O ÚLTIMO NÍVEL - REQUEST HANDLER
        if (1 === count($this->map)) {
            return $this->getInstanceMap()->handle($request);
        }

        //EXECUTA OS MIDDLEWARES
        return $this->getInstanceMap()->process($request, $this);

      }catch(\Exception $e){
        return new ResponseException($e);
      }
    }
}

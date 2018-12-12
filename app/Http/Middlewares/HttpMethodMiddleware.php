<?php

namespace App\Http\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 *
 * Implementação de MiddlewareInterface responsável por validar o método HTTP da requisição
 *
 * @author William Costa
 *
 */
class HttpMethodMiddleware implements MiddlewareInterface {

  private $method;

  /**
   * Responsável por atribuir o método permitido na requisição
   * @method __construct
   * @param  array       $params
   */
  public function __construct(array $params = []){
    list($method) = $params;
    $this->method = !empty($method) ? $method : 'GET';
  }

  /**
   * Método responsável por processar a requisição
   * @method process
   * @param  ServerRequestInterface  $request
   * @param  RequestHandlerInterface $handler
   * @return ResponseInterface
   */
  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    $serverParams = $request->getServerParams();

    //VALIDAÇÃO DO MÉTODO
    if(!isset($serverParams['REQUEST_METHOD']) or $serverParams['REQUEST_METHOD'] != $this->method){
      throw new \Exception('Somente requisições '.$this->method.' são permitidas', 405);
    }

    //RESPONSE
    $response = $request->getResponseBody();
    $response[] = [
                    'middleware'=>'Requisição '.$this->method,
                    'sucesso'=>true
                  ];
    $request->setResponseBody($response);

    return $handler->handle($request);
  }

}

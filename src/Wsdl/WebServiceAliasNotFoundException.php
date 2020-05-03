<?php

declare(strict_types=1);


namespace Skaut\Skautis\Wsdl;


use Throwable;

class WebServiceAliasNotFoundException
  extends
  WsdlException
{

  public function __construct(
    string $alias,
    int $code = 0,
    Throwable $previous = null
  ) {
    parent::__construct("Alias '$alias' does not exist", $code, $previous);
  }

}

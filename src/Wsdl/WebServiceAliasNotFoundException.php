<?php

declare(strict_types=1);


namespace Skaut\Skautis\Wsdl;


use Skaut\Skautis\Exception;
use Throwable;

class WebServiceAliasNotFoundException
  extends
  \RuntimeException
  implements
  Exception
{

  public function __construct(
    string $alias,
    int $code = 0,
    Throwable $previous = null
  ) {
    parent::__construct("Alias '$alias' does not exist", $code, $previous);
  }

}

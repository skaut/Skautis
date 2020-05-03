<?php

declare(strict_types=1);


namespace Skaut\Skautis\Wsdl;


use Throwable;

class WebServiceNotFoundException
  extends
  WsdlException
{

  public function __construct(
    string $name,
    int $code = 0,
    Throwable $previous = null
  ) {
    parent::__construct("Webservice '$name' is not known by the Skautis library'", $code, $previous);
  }

}

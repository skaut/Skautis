<?php

declare(strict_types=1);


namespace Skautis\Wsdl;


use Skautis\Exception;
use Throwable;

class WebServiceNotFoundException
  extends
  \RuntimeException
  implements
  Exception
{

  public function __construct(
    $name,
    $code = 0,
    Throwable $previous = null
  ) {
    parent::__construct("Webservice '$name' is not known by the Skautis library'", $code, $previous);
  }

}
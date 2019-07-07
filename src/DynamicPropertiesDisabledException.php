<?php

declare(strict_types=1);


namespace Skautis;


class DynamicPropertiesDisabledException
  extends
  \RuntimeException
  implements
  Exception
{

  public function __construct(
    $message = 'This class does not support dynamic properties'
  ) {
    parent::__construct($message);
  }

}
<?php

declare(strict_types=1);


namespace Skautis\Wsdl;

class MaintenanceErrorException
  extends
  \RuntimeException
{

  /**
   * @var int
   */
  private $errno;

  /**
   * @var string
   */
  private $errfile;

  /**
   * @var int
   */
  private $errline;

  public function __construct(
    string $message,
    int $errno,
    string $errfile,
    int $errline
  ) {
    parent::__construct($message, 0);

    $this->errno = $errno;
    $this->errfile = $errfile;
    $this->errline = $errline;
  }

  public function getErrorNumber(): int
  {
    return $this->errno;
  }

  public function getErrorFile(): string
  {
    return $this->errfile;
  }

  public function getErrorLine(): int
  {
    return $this->errline;
  }

}
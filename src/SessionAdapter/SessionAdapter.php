<?php
declare(strict_types = 1);

namespace Skaut\Skautis\SessionAdapter;

/**
 * Adapter pro pouziti $_SESSION ve SkautISu
 */
class SessionAdapter implements AdapterInterface
{

    /**
     * @var array<string, mixed>
     */
    protected $session;

    public function __construct()
    {
      $sessionId = '__' . __CLASS__;
      if (!isset($_SESSION[$sessionId])) {
            $_SESSION[$sessionId] = [];
      }

        $this->session = &$_SESSION[$sessionId];
    }

    /**
     * @inheritdoc
     */
    public function set(string $name, $object): void
    {
        $this->session[$name] = $object;
    }

    /**
     * @inheritdoc
     */
    public function has(string $name): bool
    {
        return isset($this->session[$name]);
    }

    /**
     * @inheritdoc
     */
    public function get(string $name)
    {
        return $this->session[$name];
    }
}

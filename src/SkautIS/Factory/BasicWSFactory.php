<?php

namespace Skautis\Factory;

use Skautis\Factory\WSFactory;
use Skautis\WS;

/**
 * @inheritdoc
 */
class BasicWSFactory extends WSFactory {

    /**
     * @inheritdoc
     */
    public function createWS($wsdl, array $init, $compression, $profiler) {
        return new WS($wsdl, $init, $compression, $profiler);
    }

}

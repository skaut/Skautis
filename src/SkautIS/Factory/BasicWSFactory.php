<?php

namespace SkautIS\Factory;

use SkautIS\Factory\WSFactory;
use SkautIS\WS;

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

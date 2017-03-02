<?php
/**
 * NiletPHP - Simple and lightweight web MVC framework
 * (c) Tsvetelin Tsonev <github.tsonev@yahoo.com>
 * For copyright and license information of this source code, please view the LICENSE file.
 */

namespace Nilet\Components\Container;

/**
 * @author Tsvetelin Tsonev <github.tsonev@yahoo.com>
 */
class MissingResolvedDependencyException extends \Exception {
    
    public function __construct(string $message) {
        parent::__construct($message);
    }
    
}

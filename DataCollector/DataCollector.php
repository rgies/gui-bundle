<?php

/**
 * Symfony2 - Rapid Development Bundle.
 *
 * @package     RGies\GuiBundle\DataCollector
 * @author      Robert Gies <mail@rgies.com>
 * @copyright   Copyright © 2014 by Robert Gies
 * @license     MIT
 * @date        2014-07-27
 */

namespace RGies\GuiBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector AS DataCollectorBase;

class DataCollector extends DataCollectorBase
{

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }


    public function getName()
    {
        return 'guibundle_rad';
    }
}

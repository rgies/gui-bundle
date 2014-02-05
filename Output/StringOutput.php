<?php
/**
 * Symfony2 - Rapid Development Bundle.
 *
 * @package     RGies\RadBundle\Output
 * @author      Robert Gies <mail@rgies.com>
 * @copyright   Copyright © 2014 by Robert Gies
 * @license     MIT
 * @date        2014-02-04
 */

namespace RGies\GuiBundle\Output;

use Symfony\Component\Console\Output\Output;

/**
 * Class StringOutput
 * @package RGies\GuiBundle\Output
 */
class StringOutput extends Output
{
    protected $buffer = '';

    public function doWrite($message, $newline)
    {
        $this->buffer .= $message . ($newline===TRUE ? PHP_EOL : '');
    }

    public function getBuffer()
    {
        return $this->buffer;
    }
}
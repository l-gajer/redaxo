<?php

/**
 * Layout Fuß des Backends
 * @package redaxo5
 * @version svn:$Id$
 */

$bottomfragment = new rex_fragment();
echo $bottomfragment->parse('core_bottom');
unset($bottomfragment);
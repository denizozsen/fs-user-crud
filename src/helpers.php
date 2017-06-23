<?php

//
// This file contains simple helper functions, mostly for use in debugging.
//

/**
 * 'Dump and die'. Prints the given data via var_dump() and stops the script execution immediately after that.
 *
 * @param mixed $data the data to be printed, via var_dump()
 */
function dd($data)
{
    var_dump($data);
    die();
}

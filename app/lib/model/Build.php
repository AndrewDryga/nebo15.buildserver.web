<?php
/**
 * Author: Paul Bardack paul.bardack@gmail.com http://paulbardack.com
 * Date: 07.08.14
 * Time: 20:06
 */

namespace Builder\Model;


class Build
{
    private $data;

    public function __construct($data = null)
    {
        if($data){
            $this->data = $data;
        }
    }

    public function getFilePath()
    {

    }
} 
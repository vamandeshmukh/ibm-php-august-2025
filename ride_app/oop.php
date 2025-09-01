<?php

echo "OOP in PHP";
// OOP - Inheritance, Encapsulation, Asbtraction, Polymorphism, class, object, interface  
// abstract, class, interface 

interface Usable
{

}

class User implements Usable
{
    public $id;
    public $name;
    public function __construct()
    {
    }
    public function __destruct()
    {
    }

}

class Driver extends User
{

}

$obj = new User();

echo $obj->id;


?>
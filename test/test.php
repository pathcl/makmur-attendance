<?php
require 'lib/TADFactory.php';
require 'lib/TAD.php';
require 'lib/TADResponse.php';
require 'lib/Providers/TADSoap.php';
require 'lib/Providers/TADZKLib.php';
require 'lib/Exceptions/ConnectionError.php';
require 'lib/Exceptions/FilterArgumentError.php';
require 'lib/Exceptions/UnrecognizedArgument.php';
require 'lib/Exceptions/UnrecognizedCommand.php';

use TADPHP\TADFactory;
use TADPHP\TAD;
	
$tad_factory = new TADFactory(['ip'=>'10.14.1.8']);
$tad = $tad_factory->get_instance();

var_dump($tad->get_att_log());
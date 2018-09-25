<?php
require_once 'vendor/autoload.php';

use HomeLan\Retro\Acorn\BBC\Gfx\Mode7;
use HomeLan\Core\Filesystem\Filesystem;


$oTeleText = new Mode7(
			new Filesystem()
		);

$oTeleText->loadMode7Data('/tmp/download.txt');
$oTeleText->writePng('/tmp/0-0.png');

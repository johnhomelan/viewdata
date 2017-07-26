<?php
require_once 'vendor/autoload.php';

use HomeLan\Retro\BBC\Gfx\Mode7Image;
use HomeLan\Core\Filesystem\Filesystem;


$oTeleText = new Mode7Image(
			new Filesystem()
		);

$oTeleText->loadMode7Data('/tmp/download.txt');
$oTeleText->writePng('/tmp/0-0.png');

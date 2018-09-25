Viewdata
========


PHP Class for reading Viewdata, Teletext, BBC Mode 7 Graphics, and producing jpeg, png, gif images from that data.  

Install
-------

composer require homelan/acorn-mode7

Usage
-----

There is one class Mode7, and loads raw BBC mode 7 (teletext) graphics data and produces jpeg/png/gif images from it. 

The constructor requires passing a filesystem object to it. 


use HomeLan\Retro\Acorn\BBC\Gfx\Mode7;
use HomeLan\Core\Filesystem\Filesystem;


$oTeleText = new Mode7(
			new Filesystem()
		);

Once the oject exists it can load any raw mode7 graphics data


$oTeleText->loadMode7Data('/tmp/download.txt');


It can then produce an image file of that data in different formats

$oTeleText->writePng('/tmp/0-0.png');
$oTeleText->writeJpeg('/tmp/0-0.jpg');
$oTeleText->writeGif('/tmp/0-0.gif');
$oTeleText->writeBmp('/tmp/0-0.bmp');


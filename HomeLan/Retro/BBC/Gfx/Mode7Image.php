<?php

namespace HomeLan\Retro\BBC\Gfx;

use Exception;

class Mode7Image { 

	private $oFS;
	private $sBinaryMode7Data;
	private $hImage;

	private $iWidth = 40;
	private $iHeight = 25;
	private $iTopAndBottomBoarder = 5;
	private $iSideBoarder = 12;

	const FONT_WIDTH = 12;
	const FONT_HEIGHT = 20;

	/**
	 * Constructor with dep injection 
	 *
	 * @param object \HomeLan\Core\Filesystem\Filesystem $oFS
	*/
	public function __construct(\HomeLan\Core\Filesystem\Filesystem $oFS)
	{
		$this->oFS = $oFS;
	}

	/**
	 * Builds up an image from the raw mode 7 binary data 
	 *
	*/
	private function _buildImage()
	{
		if(is_null($this->sBinaryMode7Data)){
			throw Exception("No mode 7 graphics data has been loaded");
		}
		//The image is already built return
		if(!is_null($this->hImage)){
			return;
		}

	        $iWidthInPixcels = $this->iWidth * Mode7Image::FONT_WIDTH + 2 * $this->iSideBoarder;
		$iHeightInPixcels = $this->iHeight * Mode7Image::FONT_HEIGHT + 2 * $this->iTopAndBottomBoarder; 

		$this->hImage = imagecreate($iWidthInPixcels,$iHeightInPixcels);

		//Load Fonts
		$hFontRegular = imageloadfont(__DIR__."/Font/vvttxt.gdf");
		$hFontDoubleHightTop = imageloadfont(__DIR__."/Font/vvttxtop.gdf");
		$hFontDoubleHightBottom = imageloadfont(__DIR__."/Font/vvttxbtm.gdf");


		//Create the colour pallet for the image as the original BBC colour scheme
		for ($i = 0; $i < 8; $i++){
			$aColourMap[$i] = imagecolorallocate($this->hImage, ($i & 1)?255:0, ($i & 2)?255:0, ($i & 4)?255:0);
        	}

		$aTextPointers = [ 'xpos'=>0, 'ypos'=>0, 'pointer'=>0 ];
		$bDoubleBottom = false;
		$bNextBottom = false;
		$aPrev = array();

		// starting textpointer position
		while ($aTextPointers['pointer'] < strlen($this->sBinaryMode7Data)) {
			$this->_renderLine($aTextPointers,$bDoubleBottom,$bNextBottom,$aPrev,$hFontRegular,$hFontDoubleHightTop,$hFontDoubleHightBottom);
		}
	}

	/**
	 * Renders a single line of the image
	 *
	*/
	private function _renderLine(&$aTextPointers, &$bDoubleBottom, &$bNextBottom, &$aPrev, $hFontRegular, $hFontDoubleHightTop, $hFontDoubleHightBottom)
	{
		// starting forground and background colours
		$iColourForground = 7;
		$iColourBackground = 0;

		$flash = 0; // flashing off
		$graphics = 0; // text mode
		$seperated = 0; // normal graphics
		$holdgraph = 0; // hold mode off
		$holdchar = 32; // default hold char
		$conceal = 0;
		$black = 0;
		$flasher = 0;
		$flashcycle = 0;
		$bDouble = false; // doubleheight off

        	if ($flasher > 0) $flashcycle++; 

		while ($aTextPointers['pointer'] < strlen($this->sBinaryMode7Data)) {
			$char = ord($this->sBinaryMode7Data[$aTextPointers['pointer']]); // int!

			if ($bDoubleBottom) { // if we're on the bottom row of a double height bit
				$char = $aPrev[$aTextPointers['xpos']]; // use character from aPrevious row!
			} else { // otherwise
				$aPrev[$aTextPointers['xpos']] = $char; // store this character for next time ..
			}

			$hFont = $hFontRegular;
	 		// strip top bit in image files
        		$char = $char & 127;

                        // save last graphics char for hold mode
			if (($char & 32) && $graphics) $holdchar = $char;
			if ($char < 32) {
				switch ($char + 128) { // just for consistenaTextPointers['ypos'] ** remove this**
					case 128:                   // black
						if ($black != 1) {
							break;
						}
					case 129:                       // other coours
					case 130:
					case 131:
					case 132:
					case 133:
					case 134:
					case 135:
						$iColourForground = $char;
						$graphics = 0;
						$conceal = 0;
						break;
					case 136:               // flash on
						$flash = 1;
						break;
					case 137:               // flash off
						$flash = 0;
						break;
					case 140:               // double height off
						$bDouble = false;
						break;
					case 141:               // double height on
						if (!$bDoubleBottom) $bNextBottom = true;
						$bDouble = true;
						break;
					case 144;               // black graphics
						if ($black != 1) {
							break;
						}
					case 145:               // other colours
					case 146:
					case 147:
					case 148:
					case 149:
					case 150:
					case 151:
						$iColourForground = $char-16;
						$graphics = 1;
						$conceal = 0;
						break;
					case 152:               // conceal
						$conceal = 1;
						break;
					case 153:               // contiguous grapohics
						$seperated = 0;
						break;
					case 154:               // seperated graphics
						$seperated = 1;
						break;
					case 156:               // black background
						$iColourBackground = 0;
						break;
					case 157:               // new background (i.e. same as foreground)
						$iColourBackground = $iColourForground;
						break;
					case 158:               // hold graphics mode on
						$holdgraph = 1;
						break;
					case 159:               // hold graphics mode off
						$holdgraph = 0;
						break;

					default:              // ignore all other control codes
						break;
				} // switch
                		$char = 32;             // all codes display as a space unless hold mode on.
                		if ($holdgraph == 1 && $graphics == 1) $char = $holdchar;
            		}
			// are we a flasher - i.e. is anything visible flashing?
			if ($flash == 1 && $char > 32) {
				$flasher = 1;
                		if ($flashcycle == 1) $char = 32;
			}
			// concealed text does not display
			if ($conceal) $char = 32;
			// only bottom of double height chars show up on line below a d.h character
			if ($bDoubleBottom && !$bDouble) $char = 32;
			// offset to get graphics characters within fontfile
			if ($graphics) {
				if ($char & 32) { // actual graphics and not "blast through caps"
					$char += 96;
					if ($char >= 160) $char -= 32;
					if ($seperated) $char += 64;
				}
			}
			// switch to alternate font files for double height
			if ($bDouble) {
				if ($bDoubleBottom) {
					$hFont = $hFontDoubleHightBottom;
				} else {
					$hFont = $hFontDoubleHightTop;
				}
			}
			// OK we now have everything we need to write a character!
			// draw background colour
			imagefilledrectangle($this->hImage, $this->iSideBoarder + ($aTextPointers['xpos'] * Mode7Image::FONT_WIDTH), $this->iTopAndBottomBoarder + ($aTextPointers['ypos'] * Mode7Image::FONT_HEIGHT), $this->iSideBoarder + (($aTextPointers['xpos'] + 1) * Mode7Image::FONT_WIDTH-1), $this->iTopAndBottomBoarder + (($aTextPointers['ypos'] + 1) * Mode7Image::FONT_HEIGHT-1) , $iColourBackground);
			// draw character
			if ($char > 32) imagestring($this->hImage, $hFont , $this->iSideBoarder + ($aTextPointers['xpos'] * Mode7Image::FONT_WIDTH) , $this->iTopAndBottomBoarder + ($aTextPointers['ypos'] * Mode7Image::FONT_HEIGHT) , chr($char) , $iColourForground);
			// next..
			$aTextPointers['xpos']++;
			// while textpointer 
			if ($aTextPointers['xpos'] >= $this->iWidth) {
				$aTextPointers['xpos'] = 0;
				$aTextPointers['ypos']++;
				if ($aTextPointers['ypos'] >= $this->iHeight) {
					$aTextPointers['ypos'] = 0;
					break;
				}	
				$aTextPointers['pointer']++;
				$aTextPointers['pointer']++;
				$bDoubleBottom = $bNextBottom;
				$bNextBottom = false;
				return;
			}else{
				$aTextPointers['pointer']++;
			}
		}
	}

	/**
	 * Loads a data file containing raw BBC mode 7 screen data
	 *
	 * @param string $sFileName
	*/
	public function loadMode7Data($sFileName)
	{
		if(!is_null($this->hImage)){
			imagedestroy($this->hImage);
			$this->hImage = null;
			echo "reset\n";
		}
		$this->sBinaryMode7Data = $this->oFS->getContents($sFileName);
	}

	/**
	 * Writes a jpeg of the Mode 7 image
	 *
	 * @param string $sFile The filename to write the jpeg to
	*/
	public function writeJpeg($sFile)
	{
		$this->_buildImage();
		imagejpeg($this->hImage,$sFile);
	}	

	/**
	 * Writes a pnd of the Mode 7 image
	 *
	 * @param string $sFile The filename to write the png to
	*/
	public function writePng($sFile)
	{
		$this->_buildImage();
		imagepng($this->hImage,$sFile);
	}	

	/**
	 * Writes a gif of the Mode 7 image
	 *
	 * @param string $sFile The filename to write the gif to
	*/
	public function writeGif($sFile)
	{
		$this->_buildImage();
		imagegif($this->hImage,$sFile);
	}	

	/**
	 * Writes a bmp of the Mode 7 image
	 *
	 * @param string $sFile The filename to write the bmp to
	*/
	public function writeBmp($sFile)
	{
		$this->_buildImage();
		imagebmp($this->hImage,$sFile);
	}	
}

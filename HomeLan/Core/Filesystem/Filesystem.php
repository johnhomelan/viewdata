<?php

namespace HomeLan\Core\Filesystem;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class Filesystem extends \Symfony\Component\Filesystem\Filesystem 
{

	/**
	 * Gets the connents of a given file
	 *
	 * @param string $sFileName
	 * @return string 
	*/
	public function getContents($sFileName)
	{
	        if (stream_is_local($sFileName) && !is_file($sFileName)) {
        	    throw new FileNotFoundException(sprintf('Failed to open "%s" because file does not exist.', $sFileName), 0, null, $sFileName);
		}
		return file_get_contents($sFileName);
	}
}

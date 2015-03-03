<?php

namespace IconBuilder;

class IconBuilder {

	protected $images = array();

	public function addImageFromFilename( $filepath ) {
		$image = self::loadImageFile($filepath);

		$this->addImageData( $image );
	}

	public function saveAsIco( $filename ) {

		if ( false === ( $data = $this->buildIcoData() ) ) {
			return false;
		}

		if ( false === ( $fh = fopen( $filename, 'w' ) ) ) {
			return false;
		}

		if ( false === ( fwrite( $fh, $data ) ) ) {
			fclose( $fh );
			return false;
		}

		fclose( $fh );

		return true;
	}

	/**
	 * Generate the final ICO data by creating a file header and adding the image data.
	 */
	protected function buildIcoData() {
		if (! $this->images) {
			throw new \Exception("No images added");
		}

		$data = pack( 'vvv', 0, 1, count( $this->images ) );
		$pixelData = '';

		$iconDirEntrySize = 16;

		$offset = 6 + ( $iconDirEntrySize * count( $this->images ) );

		foreach ( $this->images as $image ) {
			$data .= pack( 'CCCCvvVV',
				$image->getWidth(),
				$image->getHeight(),
				0, // color palette colors
				0,
				1,
				32, // bits per pixel
				$image->getSize(),
				$offset);

			$pixelData .= $image->getData();
			$offset += $image->getSize();
		}

		$data .= $pixelData;
		unset( $pixelData );


		return $data;
	}

	/**
	 * Take a GD image resource and change it into a raw BMP format.
	 *
	 * @access private
	 */
	function addImageData( $im ) {

		$width = imagesx( $im );
		$height = imagesy( $im );

		$pixelData = $opacityData = [];
		$currentOpacityVal = 0;

		for ( $y = $height - 1; $y >= 0; $y-- ) {
			for ( $x = 0; $x < $width; $x++ ) {
				$color = imagecolorat( $im, $x, $y );

				$alpha = ( $color & 0x7F000000 ) >> 24;
				$alpha = ( 1 - ( $alpha / 127 ) ) * 255;

				$color &= 0xFFFFFF;
				$color |= 0xFF000000 & ( $alpha << 24 );

				$pixelData[] = $color;


				$opacity = ( $alpha <= 127 ) ? 1 : 0;

				$currentOpacityVal = ( $currentOpacityVal << 1 ) | $opacity;

				if ( ( ( $x + 1 ) % 32 ) == 0 ) {
					$opacityData[] = $currentOpacityVal;
					$currentOpacityVal = 0;
				}
			}

			if ( ( $x % 32 ) > 0 ) {
				while ( ( $x++ % 32 ) > 0 )
					$currentOpacityVal = $currentOpacityVal << 1;

				$opacityData[] = $currentOpacityVal;
				$currentOpacityVal = 0;
			}
		}

		$imageHeaderSize = 40;
		$colorMaskSize = $width * $height * 4;
		$opacityMaskSize = ( ceil( $width / 32 ) * 4 ) * $height;


		$data = pack( 'VVVvvVVVVVV', 40, $width, ( $height * 2 ), 1, 32, 0, 0, 0, 0, 0, 0 );

		foreach ( $pixelData as $color )
			$data .= pack( 'V', $color );

		foreach ( $opacityData as $opacity )
			$data .= pack( 'N', $opacity );

		$dataSize = $imageHeaderSize + $colorMaskSize + $opacityMaskSize;
		$this->images[] = new IconData($width, $height, $dataSize, $data);
	}

	/**
	 * Read in the source image file and convert it into a GD image resource.
	 */
	protected static function loadImageFile( $filename ) {

		$size = getimagesize($filename);
		if ($size === false) {
			throw new \Exception("File `$filename` does not appear to be an image");
		}

		$contents = file_get_contents($filename);
		if (! $contents) {
			throw new \Exception("File `$filename` appears to be empty");
		}

		$image = imagecreatefromstring($contents);
		if (! $image) {
			throw new \Exception("File `$filename` could not be loaded");
		}

		unset($contents);
		return $image;
	}
}
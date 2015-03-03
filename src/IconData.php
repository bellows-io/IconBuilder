<?php

namespace IconBuilder;

class IconData {

	protected $width;
	protected $height;
	protected $size;
	protected $data;

	public function __construct($width, $height, $size, $data) {
		$this->width = $width;
		$this->height = $height;
		$this->size = $size;
		$this->data = $data;
	}

	public function getWidth() {
		return $this->width;
	}

	public function getHeight() {
		return $this->height;
	}

	public function getSize() {
		return $this->size;
	}

	public function getData() {
		return $this->data;
	}

}
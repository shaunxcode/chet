<?php
namespace Model;

class TypeHex extends Primitive {
	public function asFloat() {
		return new TypeFloat(hexdec($this->value));
	}
}
register('Hex', '\Model\TypeHex');
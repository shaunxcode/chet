<?php

namespace Model;
require 'Model.php';

class Date Extends Model {
	public $day = Int;
	public $month  = Int;
	public $year = Int;
	
	public function getDayOfWeek() {
	}
	
	public function __toString() {
		return "{$this->day}/{$this->month}/{$this->year}";
	}
}
register('Date');

class Time extends Model {
	public $hour = Int;
	public $minute = Int;
	public $second = Int;
}
register('Time');


class DateTime extends Model {
	public $date = Date;
	public $time = Time;
}
register('DateTime');


class Emotion extends Model {
	public $name = String;
	public $weight = Float;
}
register('Emotion');

class Color extends Model {
	public $name = String;
	public $hexCode = Hex;
	public $feeling = Emotion;
}
register('Color');


class Tag extends Model {
	public $name = String;
}
register('Tag');

class Person extends Model {
	public $name = String;
	public $age = Int;
	public $eyeColor = Color;
	public $tags = ManyTag;
	public $dateOfBirth = Date;
	public $creationTime = DateTime;
}
register('Person');

$p = new Person(
	eyeColor, new Color(
		hexCode, 'ff00ff',
		feeling, new Emotion(
			name, 'Sad Face')),
	dateOfBirth, new Date(
		day, 26,
		month, 7,
		year, 1982));

var_dump($p->tags);
echo "_____\n\n";
echo $p->eyeColor->feeling->name->reverse->upper->md5;
echo "\n";
echo $p->eyeColor->feeling->name;
echo "\n";
echo $p->eyeColor->hexCode->asFloat;
echo "\n";
echo $p->dateOfBirth . "\n";
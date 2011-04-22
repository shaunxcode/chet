	_____________/\/\______________________/\/\_____ 
	___/\/\/\/\__/\/\__________/\/\/\____/\/\/\/\/\_ 
	_/\/\________/\/\/\/\____/\/\/\/\/\____/\/\_____
	_/\/\________/\/\__/\/\__/\/\__________/\/\_____ is a light weight, experimental,
	___/\/\/\/\__/\/\__/\/\____/\/\/\/\____/\/\/\___ pre-production-ready, REST oriented
	________________________________________________ php 5.3+ web framework

The name is taken from the late, great, Chet Baker.

php 5.3 + only as it fully uses and abuses all of the features php has. Namespaces, __callStatic, lambdas etc. 

For a basic site index.php is as simple as: 

	namespace Chet;
	
	require_once '../Chet.php';

	use \Model\Person;
	
	Container('site'); 

	Put('/person', function() { 
		return array(
			'id' => Person::create(Params()));});
		
	Get('/person/$id', function($id) {
		return array(
			'person' => Person::get($id));});
			
	Get('/people', function() { 
		return array(
			'people' => Person::getAll());});

	Dispatch();

The Container function sets the overall, well, container to be used by the site. Thus you may call it inside of an action if you need to. 

Params is a function which returns the post/request/url variables. Param('name', [default = false]) returns a given param, if not found it returns the default. In the case of Get person the 'id' param can be accessed via Param('id') or as shown you can define it as an argument of the action. 

If an action returns an associative array it is extracted before loading the view so the variables may be directly accessed i.e. in the last example the $people variable would be accessible. 

If a request is made with a .json suffix returned value of the action is given as json circumventing the rest of the site dispatching.

The view is assumed to be the name of the route up until variable capture so in the last case it would look for a view called 'people.php' in the views directory. 

Not all views have to have explicit routes/actions. If no route is matched but a view of the same name exists it will use that. This is nice for content which does not need to communicate with models/business logic. 

One of the greatest vices Chet has is found on the view side of things. Rather than using markup and or some sort of custom templating language/parser I have opted to take the functional approach. If you have ever used CLWHO you should feel at home:

	namespace html;

	output(
		div(id, 'mainDiv',
			ul(id, 'PeopleList',
				array_map(function($person) { 
					return li(klass, 'person', 
						a('/person/'. $person->id, $person->name));}, $people))));

All tags have their singular and plural forms. All attributes are constants which evaluate to their string form plus chr(0), making it possible to have keyword pairs interspersed with inner content for the tag. The only exceptions are the a tag which has a plural form of _as as "as" is a reserved keyword in php and class is klass as class is a reserved keyword. Thus the following are equivalent: 

	output(
		div(klass, 'box', 'a'), 
		div(klass, 'box', 'b'), 
		div(klass, 'box', 'c'));

	output(
		divs(klass, 'box', 'a', 'b', 'c'));

CSS is supported in a similar manner allowing mixins, inheritance etc. much like turbine, https://github.com/SirPepe/Turbine,  (in fact the examples below are mostly adapted from the turbine docs). All css properties are defined as constants with any dash being replaced with an underscore. 

	output(
		style(
			Selector('#mainDiv', 
				mixin, roundedCorners(10), 
				margin, 'auto',
				border, '1px solid ' . Color('Borders'),
				padding, Size('Boxes')))); 
	
	#S is a shortcut for Selector which comes in handy when defining nested styles
	
	S('#foo, #bar',
	    color, '#FF0000',
	    margin_left, '4px',
	    margin_right, '4px',
	    S('div.alpha, div.beta',
	        font_weight, 'bold',
	        border_radius, '4px'))

	#yields 
	
	#foo, #bar {
        color: #FF0000;
        margin-left: 4px;
        margin-right: 4px;
    }
    #foo div.alpha, #foo div.beta, #bar div.alpha, #bar div.beta {
        font-weight: bold;
        border-radius: 4px;
    }

	#The usefulness of this all is more apparent with something like:
	
	S('#header, #footer',
	    S('ul, ol, p',
	        S('a:link, a:visited',
	            text_decoration, 'underline'),
	        S('a:active, a:hover',
	            text_decoration, 'none')))
	
	#yields
	
	#header ul a:link, #header ul a:visited,
	#header ol a:link, #header ol a:visited,
	#header p a:link, #header p a:visited,
	#footer ul a:link, #footer ul a:visited,
	#footer ol a:link, #footer ol a:visited,
	#footer p a:link, #footer p a:visited {
		text-decoration: underline;
	}
	#header ul a:active, #header ul a:hover,
	#header ol a:active, #header ol a:hover,
	#header p a:active, #header p a:hover,
	#footer ul a:active, #footer ul a:hover,
	#footer ol a:active, #footer ol a:hover,
	#footer p a:active, #footer p a:hover {
		text-decoration: none;
	}
	
   
Models: 
The chet ORM provides a nice wrapper around the basic PDO crud but also yields an expressive and minimal approach to defining relationships. By registering a model once you ahve defined the class you are in fact creating a valid "type" which can be used in future model definitions in both a singular and plural manner (by prefixing it w/ Many). All of the properties of the model are also defined as constants making definition of objects similar to the CSS magic. e.g: 

	namespace Model;

	class Color extends Model {
		public $name = String;
		public $hex = Hex;
	}
	register('Color');

	class Gender extends Model {
		public $name = String;
	}

	class Person extends Model {
		public $name = String;
		public $gender = Gender;
		public $eyeColor = Color;
		public $hairColor = Color;
	}
	register('Person');

	class Family extends Model {
		public $surName = String;
		public $father = Person;
		public $mother = Person;
		public $children = ManyPerson;
	}
	register('Family');

	$genders = new Collection(
		new Gender(name, 'male'),
		new Gender(name, 'female'));
	$genders->keyBy('name');

	$colors = new Collection(
		new Color(
			name, 'blue',
			hex, new Hex('0000FF')),
		new Color(
			name, 'blonde',
			hex, new Hex('FF00FF')),
		new Color(
			name, 'green', 
			hex, new Hex('00FF00')),
		new color(
			name, 'black',
			hex, new Hex('000000')));
	$colors->keyBy('name');

	$family = new Family(
		surName, 'Wilson',
		father, new Person(
			name, 'peter',
			gender, $genders(male);
			eyeColor, $colors(blue),
			hairColor, $colors(blonde)),
		mother, new Person(
			name, 'mary',
			gender, $genders(female),
			eyeColor, $colors(green),
			hairColor, $colors(black)),
		children, new Collection(
			new Person(
				name, 'phillip',
				gender, $genders(male),
				eyeColor, $colors(blue),
				hairColor, $colors(black)),
			new Person(
				name, 'shera',
				gender, $genders(female),
				eyeColor, $colors(green), 
				hairColor, $colors(blonde))));


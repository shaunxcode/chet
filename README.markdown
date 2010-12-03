	_____________/\/\______________________/\/\_____
	___/\/\/\/\__/\/\__________/\/\/\____/\/\/\/\/\_
	_/\/\________/\/\/\/\____/\/\/\/\/\____/\/\_____
	_/\/\________/\/\__/\/\__/\/\__________/\/\_____
	___/\/\/\/\__/\/\__/\/\____/\/\/\/\____/\/\/\___
	________________________________________________ is a light weight, experimental, pre-production-ready, REST oriented php framework. 

The name is taken from the late, great, Chet Baker.

php 5.3 + only as it fully uses and abuses all of the features php has. Namespaces, __callStatic, lambdas etc. 

For a basic site index.php is as simple as: 

	namespace Chet;

	require_once '../Chet.php';

	Get('/person/$id', function($id) {
		return array(
			'person' => Person::get($id));});

	Get('/people', function() { 
		return array(
			'people' => Person::getAll());});

	Dispatch();

The view is assumed to be the name of the route up until variable capture so in the last case it would look for a view called 'people.php' in the views directory. 

One of the greatest vices Chet has is found on the view side of things. Rather than using markup and or some sort of custom templating language/parser I have opted to take the functional approach. If you have ever used CLWHO you should feel at home:

	namespace html;

	output(
		div(id, 'mainDiv',
			ul(id, 'PeopleList',
				array_map(function($person) { 
					return li(klass, 'person', 
						a('/person/'. $person->id, $person->name));}, $people))));

All tags have their singular and plural forms. All attributes are constants which evaluate to their string form plus chr(0), making it possible to have keyword pairs interspersed with inner content for the tag. The only exceptions are the a tag which has a plural form of _as as "as" is a reserved keyword in php and class is klass as class is a reserved keyword. Thus the following are equivelant: 

	output(
		div(klass, 'box', 'a'), 
		div(klass, 'box', 'b'), 
		div(klass, 'box', 'c'));

	output(
		divs(klass, 'box', 'a', 'b', 'c'));

CSS is supported in a similar manner allowing mixins, inheritence etc. much like turbine. 

	output(
		style(
			Selector('#mainDiv', 
				mixin, roundedCorners(10), 
				margin, 'auto',
				border, '1px solid ' . Color('Borders'),
				padding, Size('Boxes')))); 

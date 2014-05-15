# Passing variables to the view

### As parameter

We can pass variable to the view by using the second parameter inside the `loadView()` method.

	$sheet->loadView('view', array('key' => 'value'));

### With with()

Alternativly you can use the `with()` method which works the same as with Laravel views.

	// Using normal with()
	$sheet->loadView('view')
		  ->with('key', 'value');

	// using dynamic with()
	$sheet->loadView('view')
		  ->withKey('value');
<?php

namespace Maatwebsite\Excel\Facades;

use Illuminate\Support\Facades\Facade;

class Excel extends Facade {

	protected static function getFacadeAccessor()
	{
		return 'excel';
	}

}
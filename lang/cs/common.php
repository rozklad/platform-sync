<?php

return [
	'title' => 'Synchronization',
	'import' => 'Import',
	'export' => 'Export',
	'preview' => 'Preview',

	'functions' => [
		'ignore' => 'Ignorovat',
		'create_attribute' => 'Vytvořit atribut',
	],

	'options' => [
		'use_dictionary' => 'Používat základní slovník',
	],

	'messages' => [
		'errors' => [
			'no_file' => 'No file has been uploaded',
		],
		'success' => [
			'imported' => 'Data imported succesfully'
		]
	],

	'help' => [

		'refresh' => [

			'title' => 'Obnovení exportu',
			'description' => 'Všechny exportní soubory jsou znovu vygenerovány ve chvíli, kdy dojde k jakékoliv změně produktu. Typicky se může jednat o změnu ceny, zařazení v kategorii apod.',
			'cron' => 'Protože všechny exporty jsou upravovány průběžně, není třeba nastavovat za tímto účelem CRONovou operaci. Pokud si přesto přejete operaci nastavit, pod tímto textem naleznete odkaz.',
		
		],

	],

	'max_allowed_size' => 'Maximální dovolená velikost souboru je :size',

];
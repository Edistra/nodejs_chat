<?php
	
	/* tableau de config de la grille */
	
	$grid_conf = array(
		'pas' => array(
			'x' => array(
				'ecranXl' => array(
					'unite' => 'px',
					'value' => 0,
					'value_back' => 0
				),
                'ecran' => array(
					'unite' => 'px',
					'value' => 80,
					'value_back' => 80
				),
				'tablette' => array(
					'unite' => 'px',
					'value' => 60,
					'value_back' => 60
				),
				'mobile' => array(
					'unite' => '%',
					'value' => 8.33,
					'value_back' => 24
				)
			),
			'y' => array(
                'ecranXl' => array(
                    'unite' => 'px',
                    'value' => 0,
                    'value_back' => 0
                ),
                'ecran' => array(
					'unite' => 'px',
					'value' => 10,
					'value_back' => 10
				),
				'tablette' => array(
					'unite' => 'px',
					'value' => 10,
					'value_back' => 10
				),
				'mobile' => array(
					'unite' => 'px',
					'value' => 10,
					'value_back' => 10
				)
			)
		),
		
		/* Les diff�rentes variables de composition d'un bloc < nom dans la base > => array ( < nom css >, < pas de la grille li� > ) */
		'variables' => array(
			'width' => array( 'width', 'x'),
			'height' => array( 'height', 'y'),
			'posx' => array( 'left', 'x'),
			'posy' => array( 'top', 'y')
		),
		'col' => 12,
		'row' => 100
	);
?>
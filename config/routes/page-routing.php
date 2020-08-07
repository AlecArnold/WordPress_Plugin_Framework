<?php
/**
 * Contains the routing configuration for loading the page routing module.
 *
 * @package Plugin_Name
 */

return array(
	'route_page' => array(
		'callback' => 'Plugin_Name\Module\Page_Routing\Page_Router@route_page',
	),
);

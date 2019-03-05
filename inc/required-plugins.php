<?php

/* check and install required plugins */

function i2019_register_required_plugins() {
	$plugins = array(
		array(
			'name'               => 'GitHub Updater',
			'slug'               => 'github-updater',
			'source'             => 'https://github.com/afragen/github-updater/archive/master.zip',
			'required'           => true,
			'version'            => '8.7.0',
		),
	);

	$config = array(
		'id'           => 'i2019',
		'default_path' => '',
		'menu'         => 'tgmpa-install-plugins',
		'parent_slug'  => 'themes.php',
		'capability'   => 'edit_theme_options',
		'has_notices'  => true,
		'dismissable'  => true,
		'dismiss_msg'  => '',
		'is_automatic' => true,
		'message'      => '',
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'i2019' ),
			'menu_title'                      => __( 'Install Plugins', 'i2019' ),
			'installing'                      => __( 'Installing Plugin: %s', 'i2019' ),
			'updating'                        => __( 'Updating Plugin: %s', 'i2019' ),
			'oops'                            => __( 'Something went wrong with the plugin API.', 'i2019' ),
			'notice_can_install_required'     => _n_noop(
				'This theme requires the following plugin: %1$s.',
				'This theme requires the following plugins: %1$s.',
				'i2019'
			),
			'notice_ask_to_update'            => _n_noop(
				'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
				'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
				'i2019'
			),
			'notice_ask_to_update_maybe'      => _n_noop(
				'There is an update available for: %1$s.',
				'There are updates available for the following plugins: %1$s.',
				'i2019'
			),
			'notice_can_activate_required'    => _n_noop(
				'The following required plugin is currently inactive: %1$s.',
				'The following required plugins are currently inactive: %1$s.',
				'i2019'
			),
			'install_link'                    => _n_noop(
				'Begin installing plugin',
				'Begin installing plugins',
				'i2019'
			),
			'update_link' 					  => _n_noop(
				'Begin updating plugin',
				'Begin updating plugins',
				'i2019'
			),
			'activate_link'                   => _n_noop(
				'Begin activating plugin',
				'Begin activating plugins',
				'i2019'
			),
			'return'                          => __( 'Return to Required Plugins Installer', 'i2019' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'i2019' ),
			'activated_successfully'          => __( 'The following plugin was activated successfully:', 'i2019' ),
			'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'i2019' ),
			'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'i2019' ),
			'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'i2019' ),
			'dismiss'                         => __( 'Dismiss this notice', 'i2019' ),
			'notice_cannot_install_activate'  => __( 'There are one or more required or recommended plugins to install, update or activate.', 'i2019' ),
			'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'i2019' ),
			'nag_type'                        => '',
		),
	);

	tgmpa( $plugins, $config );
}

add_action( 'tgmpa_register', 'i2019_register_required_plugins' );

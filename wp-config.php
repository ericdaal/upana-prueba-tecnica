<?php


/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'conceptu_wp_oykgw' );

/** Database username */
define( 'DB_USER', 'conceptu_wp_ershz' );

/** Database password */
define( 'DB_PASSWORD', 'YJB9sC9PfWXV2Y$#' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'eEWDz%i9hd0NTCkxb_S385dHT-/1;u%6dqYJbFFyUU/#16bAY)j65Ez0/689je4Q');
define('SECURE_AUTH_KEY', 'Y@7+]D8+-927o:3414SN4XOz@*%62m9&*5SwkKtVHs10c/9|7;7oJ8V!FY~Mj7)]');
define('LOGGED_IN_KEY', 'MbkJ2BVoY:FTB8l1d0G76edt&8#nv9(K/59%56O7-f%BpvG&3d55|Ja!I&_t4Tk3');
define('NONCE_KEY', '|pPR9Fh/73vMn|g(X#5-2V/Hf6LX77J|Ab8p_[K4/%9L2|u[i754d#&B]VC8b53X');
define('AUTH_SALT', 'D0b:kP!63%l27v(W8:4&uo2(8~MfiBnbc~c%sFUlz(1D7l9[k;mXbj)pq__9b*4W');
define('SECURE_AUTH_SALT', '5G(H3;Q8+~()nu6jmf|4E0-jyx7K(N];1678]z!0jPZB-jt:ekgJNR0b:d1]]#68');
define('LOGGED_IN_SALT', 'I6[O%!LTD9819]p:x(f!cw[X9:|3g28*y72a]3%p_2lf:b]-cqE93)*eOUN2PLHO');
define('NONCE_SALT', '34Gp4w/i/VC/mH1h)3|K0r@jKc(Ql0/5E!5+f4:GAZ@*LO)e@y4U9Hz7(r@(#9|2');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'kRHHUChD_';


/* Add any custom values between this line and the "stop editing" line. */

define('WP_ALLOW_MULTISITE', true);
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

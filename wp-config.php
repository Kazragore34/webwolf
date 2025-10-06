<?php
define( 'WP_CACHE', true );

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
define( 'DB_NAME', 'u571508109_Am0mN' );

/** Database username */
define( 'DB_USER', 'u571508109_MMGDZ' );

/** Database password */
define( 'DB_PASSWORD', '5bEsahtw17' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define( 'AUTH_KEY',          '$a %;JWwy=mLnl* wXjaY52A#/`P<`@XiIfZa>v`sZSvNRvw3&6gT(^*sucHLs,^' );
define( 'SECURE_AUTH_KEY',   '3`+=vN`kca#BFZ<#acNW*~ WK|qP#1-CXbySkCmJqHhMv`-PnD8vr2+`Z`o.;7%%' );
define( 'LOGGED_IN_KEY',     '0QR*f}7O|5LaGyNTnmwi`?KSAHWc$Iy~lW8rm#ifSR@VXk1y_BM=dCKDn^#TT*xt' );
define( 'NONCE_KEY',         's2bjB}($yn4ORmC=fbXhZprc1zY5Xz[;Nn2y>B(fU?Xx*g{4ADJMAv-S3]* %2 )' );
define( 'AUTH_SALT',         '}V/L0Z@GKv ,<%rrA2p~pOw/rD[/2yqkS/5toGfV>.h;ldNF{>f,cit[6;=boeTE' );
define( 'SECURE_AUTH_SALT',  '`B[po+AbJ2z(UzX{%H]j<Q)u0J=!BK_!+Fdf03&x`sL~+v,EyHD{T%L/yZY~)Ldc' );
define( 'LOGGED_IN_SALT',    'LX1 H gbHo^RlE8BmDG*Q$w.--~UH3 y5P0,&nyLuo=T,&KJk5Nbj[%R?G)4*GPN' );
define( 'NONCE_SALT',        'o <`B)I2McPkF/]F89}973Iib`d`Z8aS52Y,&1jTyKd[<6+x)5594GPO}(zcdSN.' );
define( 'WP_CACHE_KEY_SALT', 'eb802C#u!}EVye/l76I]pXdPXpIt*U%MCwvqQ5G-R^Bchs/9S<[RRN3yb+T<ikEe' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', 'dff41be8af8110588230d49c1d27ae01' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
